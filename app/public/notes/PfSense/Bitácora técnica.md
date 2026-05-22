
Claro que sí! Es una excelente práctica para consolidar lo aprendido. He estructurado todo el flujo de trabajo de hoy en un formato **Markdown** limpio, técnico y organizado por hitos, eliminando el "ruido" y centrándome en la arquitectura que construimos para **datenmaniak**.



---

## 📔 Bitácora Técnica: Implementación de Red y K3s tras Firewall pfSense

## 1. Topología de Red y Acceso

Se migró el Home Lab desde una red plana a una arquitectura segmentada para mayor seguridad y profesionalismo.

- **Segmento WAN:** `192.168.1.x` (Red principal).
    
- **Segmento LAN (Daten-Net):** `10.0.0.x` (Gestionada por pfSense).
    
- **Nodos:**
    
    - **Master:** `10.0.0.100`
        
    - **Workers:** `10.0.0.101`, `10.0.0.102`
        

### Configuración de pfSense (NAT Port Forwarding)

Para permitir el acceso desde la PC principal (WAN) hacia los servicios internos (LAN):

| **Puerto Entrada (WAN)** | **Puerto Destino (LAN)** | **IP Destino** | **Propósito**                 |
| ------------------------ | ------------------------ | -------------- | ----------------------------- |
| `6443`                   | `6443`                   | `10.0.0.100`   | Gestión con `kubectl`         |
| `8443`                   | `443`                    | `10.0.0.100`   | Tráfico HTTPS de aplicaciones |

---

## 2. Re-configuración de K3s (Migración de IP)

Al cambiar las IPs de los nodos, se debieron ajustar los certificados y la comunicación del clúster.

### Reparación de Certificados en el Master

Para evitar errores de TLS al conectar desde la WAN:

1. Detener servicio: `sudo systemctl stop k3s`.
    
2. Borrar certificados antiguos: `sudo rm -rf /var/lib/rancher/k3s/server/tls/`.
    
3. Reinstalar con TLS-SAN:
    
    
    ```bash
    curl -sfL https://get.k3s.io | sh -s - server --tls-san 192.168.1.2
    ```
    
    _(Donde `192.168.1.2` es la IP WAN del pfSense)._
    

### Limpieza de Nodos

Si aparecen nodos con nombres antiguos o en estado `NotReady`:


```bash
kubectl delete node <nombre-antiguo>
```

---

## 3. Exposición de Servicios con Traefik

K3s utiliza Traefik como Ingress Controller por defecto. Para que responda en los puertos estándar del host, se realizaron los siguientes ajustes:

### Cambio de NodePort a LoadBalancer

Por defecto, Traefik puede instalarse como `NodePort`, lo que abre puertos altos (ej. 32360). Para usar el puerto 443 real:


```bash
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "LoadBalancer"}}'
```

### Verificación de Puertos en Linux

Para confirmar que el Master está escuchando tráfico:


```bash
ss -tulpn | grep -E ":80|:443"
```

---

## 4. Despliegue y Validación (Capa 7)

Para probar el flujo completo, se desplegó un servicio de prueba y un recurso Ingress.

### El flujo del paquete

`Navegador (lab.datenmaniak.local:8443)` -> `pfSense (NAT)` -> `Master (Puerto 443)` -> `Traefik (Ingress)` -> `Service` -> `Pod (Nginx)`.

### Código de Prueba (Ingress)


```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: web-test-ingress
  annotations:
    traefik.ingress.kubernetes.io/router.entrypoints: websecure
spec:
  rules:
  - host: lab.datenmaniak.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: web-test-svc
            port:
              number: 80
```

---

## 5. Glosario de Troubleshooting (Errores comunes)

- **`curl: (7) Failed to connect`**: El puerto está cerrado. Revisar `ufw` en Ubuntu, la regla de Firewall en la WAN de pfSense o si el servicio está en modo `LISTEN`.
    
- **`404 page not found`**: ¡Éxito de red! El paquete llegó a Traefik, pero no hay un Ingress que coincida con el `host` solicitado.
    
- **`EXTERNAL-IP <pending>`**: El balanceador de K3s (ServiceLB) no ha podido asignar una IP. Revisar que no haya conflictos de IP en la red `10.0.0.x`.
    

---