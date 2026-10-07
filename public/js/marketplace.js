document.querySelectorAll('[data-auto-submit]').forEach(select => {
    select.addEventListener('change', () => select.form.requestSubmit());
});
document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
const photoEditor = document.querySelector('[data-photo-editor]');
if (photoEditor) {
    const input = photoEditor.querySelector('[data-image-input]');
    const previews = photoEditor.querySelector('[data-new-photos]');
    const message = photoEditor.querySelector('[data-photo-message]');
    let files = [];
    let previewUrls = [];

    const selectedFile = () => {
        const value = photoEditor.querySelector('input[name="primary_image"]:checked')?.value;
        return value?.startsWith('new:') ? files[Number(value.split(':')[1])] : null;
    };
    function updateSelection() {
        photoEditor.querySelectorAll('[data-existing-photo]').forEach(tile => {
            const removed = tile.querySelector('[data-remove-photo]').checked;
            const radio = tile.querySelector('input[type="radio"]');
            tile.classList.toggle('photo-removed', removed);
            radio.disabled = removed;
            if (removed) radio.checked = false;
        });
        if (!photoEditor.querySelector('input[name="primary_image"]:checked')) {
            const first = photoEditor.querySelector('input[name="primary_image"]:not(:disabled)');
            if (first) first.checked = true;
        }
        const retained = photoEditor.querySelectorAll('[data-existing-photo] input[type="radio"]:not(:disabled)').length;
        const count = retained + files.length;
        const invalidFile = files.some(file => file.size > 4 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type));
        const error = count > 8 ? 'Keep up to 8 photos. Remove photos before saving.' : invalidFile ? 'Each photo must be JPG, PNG or WebP and no larger than 4 MB.' : '';
        input.setCustomValidity(error);
        message.textContent = error || `${count} of 8 photos. Select a primary photo for search results.`;
        message.classList.toggle('danger', Boolean(error));
    }
    function render(chosenFile) {
        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];
        previews.replaceChildren();
        const transfer = new DataTransfer();
        files.forEach((file, index) => {
            transfer.items.add(file);
            const tile = document.createElement('div');
            tile.className = 'photo-tile';
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            previewUrls.push(image.src);
            image.alt = `New photo ${index + 1}: ${file.name}`;
            const label = document.createElement('label');
            label.className = 'photo-choice';
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'primary_image';
            radio.value = `new:${index}`;
            radio.checked = file === chosenFile;
            label.append(radio, ' Primary photo');
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'text-button danger';
            remove.textContent = 'Remove photo';
            remove.setAttribute('aria-label', `Remove new photo ${index + 1}`);
            remove.addEventListener('click', () => {
                const chosen = selectedFile();
                files.splice(index, 1);
                render(chosen);
            });
            tile.append(image, label, remove);
            previews.append(tile);
        });
        input.files = transfer.files;
        updateSelection();
    }
    input.addEventListener('change', () => {
        const chosen = selectedFile();
        files.push(...Array.from(input.files));
        render(chosen);
    });
    photoEditor.querySelectorAll('[data-remove-photo]').forEach(checkbox => checkbox.addEventListener('change', updateSelection));
    updateSelection();
}

document.querySelectorAll('[data-listing-gallery]').forEach(gallery => {
    const main = gallery.querySelector('[data-gallery-main]');
    const thumbnails = [...gallery.querySelectorAll('[data-gallery-photo]')];
    function show(button) {
        main.src = button.dataset.src;
        thumbnails.forEach(thumbnail => thumbnail.setAttribute('aria-pressed', String(thumbnail === button)));
    }
    thumbnails.forEach((button, index) => {
        button.addEventListener('click', () => show(button));
        button.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? thumbnails.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + thumbnails.length) % thumbnails.length;
            thumbnails[next].focus();
            show(thumbnails[next]);
        });
    });
});
