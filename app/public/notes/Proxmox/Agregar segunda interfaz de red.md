

Si prefieres la **consola de Proxmox** (línea de comandos) para ir más rápido y evitar la interfaz web, el comando es `qm set`.

Asumiendo que el **ID de tu VM de OPNsense** es, por ejemplo, **100** (cámbialo por el tuyo), ejecuta esto como root en el nodo de Proxmox:


```bash
qm set 100 --net1 virtio,bridge=vmbr1,firewall=0
```

### ¿Qué hace exactamente este comando?

- **`qm set 100`**: Modifica la configuración de la VM 100.
    
- **`--net1`**: Crea una segunda tarjeta de red (la primera es `net0`).
    
- **`virtio`**: Usa el driver de alto rendimiento (el estándar en Linux/BSD).
    
- **`bridge=vmbr1`**: Conecta esta tarjeta al puente interno que creaste.
    
- **`firewall=0`**: Desactiva el firewall nativo de Proxmox para que no interfiera con las reglas que tú definas dentro de OPNsense.
    

---

### Pasos siguientes dentro de OPNsense

Una vez ejecutado el comando, la tarjeta aparecerá "en caliente". Ahora ve a la consola de la VM (la pantalla negra de OPNsense) y haz lo siguiente:

1. **Reiniciar la asignación:** Elige la **opción 1** (_Assign interfaces_).
    
2. **¿VLANs?**: Escribe `n`.
    
3. **WAN**: Escribe `vtnet0`.
    
4. **LAN**: Escribe `vtnet1` (esta es la que acabas de agregar).
    
5. **Confirmar**: Escribe `y`.
    

Después de eso, usa la **opción 2** para ponerle la IP a la LAN (la `10.0.0.1` que hablamos) y ya estarás listo para conectar tus máquinas.

**¿Sabes cuál es el ID de tu VM?** (Aparece al lado del nombre en la lista de Proxmox).