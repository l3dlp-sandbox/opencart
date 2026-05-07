import { Controller } from '../component.js';
import { loader } from '../index.js';

export default class extends Controller {
    render() {

        return loader.template('common/home');
    }

    onClick(e) {
        e.preventDefault();

        console.log('works');

        let target = document.getElementById(e.target.getAttribute('data-target'));

        if (target.matches('x-include')) {
            target.src = e.target.getAttribute('href');
        }
    }
}