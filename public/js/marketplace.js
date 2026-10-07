document.querySelectorAll('[data-auto-submit]').forEach(select => {
    select.addEventListener('change', () => select.form.requestSubmit());
});
document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
const imageInput = document.querySelector('[data-image-input]');
const imagePreview = document.querySelector('[data-image-preview]');
let previewUrl;
if (imageInput && imagePreview) {
    imageInput.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = imageInput.files[0];
        if (!file) return;
        previewUrl = URL.createObjectURL(file);
        imagePreview.src = previewUrl;
        imagePreview.hidden = false;
    });
}
