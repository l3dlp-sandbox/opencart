import { WebComponent } from '../component.js';
import { loader } from '../index.js';

// Config
const config = await loader.config('default');

customElements.define('x-include', class extends WebComponent {
    static observed = ['src'];

    get src() {
        return this.getAttribute('src');
    }

    set src(src) {
        this.setAttribute('src', src);
    }

    async render() {
        // Get the source HTML to load
        if (!this.src) return;

        //console.log('RENDER');
        //console.log(config.config_path + this.src + '.js');

        let controller = await import(config.config_path + this.src + '.js');

        let object = new controller.default(this);

        let methods = Object.getOwnPropertyNames(Object.getPrototypeOf(object));

        for (let method of methods) {
            if (method !== 'render' && method !== 'constructor') {
                if (typeof object[method] === 'function') {
                    this[method] = object[method].bind(this);
                }
            }
        }

        let output = await object.render();

        if (output) {
            return output;
        }
    }
});