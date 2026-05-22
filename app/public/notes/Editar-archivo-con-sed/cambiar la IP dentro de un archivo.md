<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# cual es la sintaxis para cambiar la ip 127.0.0.1 por la 192.168.1.2, usando sed y el archivo

La sintaxis es:

```bash
sed -i 's/127\.0\.0\.1/192.168.1.2/g' archivo.txt
```

Si quieres ver el cambio sin modificar el archivo:

```bash
sed 's/127\.0\.0\.1/192.168.1.2/g' archivo.txt
```

Notas:

- `-i` edita el archivo directamente.
- Escapé los puntos de la IP original con `\.` para que se interpreten como puntos literales.
- `g` reemplaza todas las apariciones en cada línea.[^1_1][^1_7]

Si quieres, también te doy la versión para hacer copia de respaldo automática antes de editar.
<span style="display:none">[^1_10][^1_2][^1_3][^1_4][^1_5][^1_6][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://stackoverflow.com/questions/49367985/sed-to-replace-first-two-octets-if-they-are-192-168

[^1_2]: https://www.youtube.com/watch?v=mZG0yXWj_GU

[^1_3]: https://blog.alcancelibre.org/staticpages/index.php/08-parametros-red

[^1_4]: https://es.stackoverflow.com/questions/202549/comando-para-interfaz-para-cambiar-ip-en-linux

[^1_5]: https://docs.oracle.com/cd/E19957-01/820-2981/gbwxb/index.html

[^1_6]: https://www.cisco.com/c/es_mx/support/docs/smb/switches/cisco-350-series-managed-switches/Creating-a-text-file-to-adjust-IP-settings-on-a-switch.pdf

[^1_7]: https://www.sysadmit.com/2015/07/linux-reemplazar-texto-en-archivos-con-sed.html

[^1_8]: https://www.youtube.com/watch?v=3TrVlbSRSy8

[^1_9]: https://www.reddit.com/r/wsl2/comments/10gbnl1/change_wsl2_ip_instead_using_12001/

[^1_10]: https://www.ionos.com/es-us/digitalguide/servidores/know-how/localhost/

