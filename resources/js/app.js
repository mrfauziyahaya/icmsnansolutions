import './bootstrap';
import Alpine from 'alpinejs';
import './layout';

// Rich-text editor for blog posts. Trix registers the <trix-editor> element;
// trix-uploads wires inline image uploads to our server.
import 'trix';
import 'trix/dist/trix.css';
import './trix-uploads';

window.Alpine = Alpine;
Alpine.start();
