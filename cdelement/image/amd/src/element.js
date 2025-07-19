import ModalLightBox from 'cdelement_image/local/modal/lightbox';



/**
 * Display an alert and return the promise from it.
 *
 * @private
 * @param {String} title The title of the alert
 * @param {String} body The content of the alert
 * @returns {Promise<ModalAlert>}
 */

const displayLightBox = async(body) => {

    console.log(body);

    return ModalLightBox.create({
        body,
        removeOnClose: true,
        show: true,
    })
    .then((modal) => {
        return modal;
    });
};

export const init = () => {

    document.addEventListener('click', function(e) {
        const lightBox = e.target.closest('[data-modal="lightbox"]');

        console.log(lightBox.dataset.modalContent);

        if (lightBox) {
            e.preventDefault();
            displayLightBox(lightBox.dataset?.modalContent);
        }
    })
}
