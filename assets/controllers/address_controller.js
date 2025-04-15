import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    display_new_address(event) {
        event.preventDefault();
        document.getElementById('new-address').style.display = 'block';
        document.getElementById('location').style.display = 'none';
        event.target.innerHTML = 'Lieu existant';
        event.target.attributes['data-action'].value = 'address#display_existing_address';
    }

    display_existing_address(event) {
        event.preventDefault();
        document.getElementById('new-address').style.display = 'none';
        document.getElementById('location').style.display = 'block';
        event.target.innerHTML = 'Ajouter un nouveau lieu';
        event.target.attributes['data-action'].value = 'address#display_new_address';
    }

}
