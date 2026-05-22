
### Pasos para limpiar el clúster:

#### 1. Eliminar el nodo antiguo

Como `k8s-master` ya no existe (ahora es `k3s-master`), debes borrarlo lógicamente del clúster para que no ensucie tus comandos de `kubectl`:


```bash
kubectl delete node k8s-master
```

#### 2. Verificar las IPs internas

Para asegurarte de que todo el tráfico fluye por el bridge de pfSense (`10.0.0.x`) y no hay residuos de la red vieja, ejecuta:

Bash

```
kubectl get nodes -o wide
```

**Qué revisar en la columna `INTERNAL-IP`:**

- Todos deberían mostrar direcciones `10.0.0.x`.
    
- Si alguno muestra `192.168.1.x`, significa que ese nodo todavía tiene configurada la IP vieja en su configuración de agente de K3s.

