<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# ❯ git push

To [https://github.com/datenmaniak/obsidiannotes.git](https://github.com/datenmaniak/obsidiannotes.git)
! [rejected]        main -> main (non-fast-forward)
error: failed to push some refs to 'https://github.com/datenmaniak/obsidiannotes.git'
hint: Updates were rejected because the tip of your current branch is behind
hint: its remote counterpart. If you want to integrate the remote changes,
hint: use 'git pull' before pushing again.
hint: See the 'Note about fast-forwards' in 'git push --help' for details.

El error significa que el repositorio remoto (GitHub) tiene commits más nuevos en `main` que tu rama local, así que Git no permite hacer un “push” normal para evitar perder historial.[^1_1][^1_6][^1_8]

## Solución recomendada (segura)

En la misma carpeta del repo, haz:

```bash
git pull origin main
```

o, si quieres que el historial quede más limpio (rebase):

```bash
git pull --rebase origin main
```

Después de que `git pull` termine (y resuelvas posibles conflictos si los hay), vuelve a intentar:

```bash
git push
```

Con esto integras primero los cambios remotos y luego subes los tuyos.[^1_3][^1_8][^1_1]

***

## ¿Cuándo usar `force push`?

En rama `main` compartida **no es recomendable** usar `git push --force` porque puede borrar el trabajo de otros o del propio repo.[^1_6][^1_1]
Solo se considera en casos muy concretos (por ejemplo, rama personal que ya no comparte nadie y tras un `rebase`), y allí es mejor usar:

```bash
git push --force-with-lease origin main
```

Si comentas qué hiciste justo antes (commits, cambios en remoto, etc.), puedo indicarte el flujo exacto que te conviene.
<span style="display:none">[^1_10][^1_2][^1_4][^1_5][^1_7][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://oneuptime.com/blog/post/2026-01-24-git-push-rejected-non-fast-forward/view

[^1_2]: https://github.com/orgs/community/discussions/49593

[^1_3]: https://stackoverflow.com/questions/20467179/git-push-rejected-non-fast-forward

[^1_4]: https://github.com/github/docs/blob/main/content/get-started/using-git/dealing-with-non-fast-forward-errors.md

[^1_5]: https://www.janbasktraining.com/community/devops/how-to-solve-the-error-of-git-push-rejected-non-fast-forward

[^1_6]: https://docs.github.com/de/enterprise-cloud@latest/get-started/using-git/dealing-with-non-fast-forward-errors

[^1_7]: https://www.youtube.com/watch?v=6O13IuACCxE

[^1_8]: https://docs.github.com/es/get-started/using-git/dealing-with-non-fast-forward-errors

[^1_9]: https://docs.github.com/es/enterprise-cloud@latest/get-started/using-git/dealing-with-non-fast-forward-errors

[^1_10]: https://komodor.com/learn/how-to-fix-failed-to-push-some-refs-to-git-errors/

