


1. Seguir las instrucciones de `Crear maquinas virtuales`

2.  Asignar la **segunda interfaz de red para la LAN**

```bash
qm set 800 --net1 virtio,bridge=vmbr1,firewall=0
```

3. Iniciar la VM y comenzar la instalación del O.S.
	- En mi caso, utilizare Pfsense. 