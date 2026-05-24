// esto es lo que tengo actualmente en:resources/js/app.js
import './bootstrap';

import Alpine from 'alpinejs';

// // Importar Highlight.js
import hljs from 'highlight.js/lib/core';
// import javascript from 'highlight.js/lib/languages/javascript';


// Importar SOLO los lenguajes que necesitas
import bash from 'highlight.js/lib/languages/bash';
import yaml from 'highlight.js/lib/languages/yaml';
import php from 'highlight.js/lib/languages/php';
import javascript from 'highlight.js/lib/languages/javascript';
import python from 'highlight.js/lib/languages/python';
import sql from 'highlight.js/lib/languages/sql';
import json from 'highlight.js/lib/languages/json';
import xml from 'highlight.js/lib/languages/xml'; // HTML usa xml
import dockerfile from 'highlight.js/lib/languages/dockerfile';

// Importar tema de Highlight.js
import 'highlight.js/styles/atom-one-dark.css';

// Registrar los lenguajes
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('php', php);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('python', python);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('json', json);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('dockerfile', dockerfile);

// Inicializar Alpine
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    hljs.highlightAll();
});

// Inicializar Highlight.js después de que el DOM cargue
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });

    // document.addEventListener('DOMContentLoaded', (event) => {
    //     document.querySelectorAll('pre code').forEach((el) => {
    //         hljs.highlightElement(el);
    //     });
    // });

    document.addEventListener('DOMContentLoaded', () => {
        hljs.highlightAll();
    });

});