// Image upload handling
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('image-previews');
    const addBtn = document.getElementById('add-image-btn');
    const fileInput = document.getElementById('new-images');

    if (addBtn && fileInput) {
        addBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const box = document.createElement('div');
                    box.className = 'image-preview-box me-2 mb-2';
                    box.innerHTML = `
                        <img src="${e.target.result}" alt="">
                        <button type="button" class="remove-img">&times;</button>
                        <input type="hidden" name="existing_images[]" value="">
                    `;
                    // Store file reference somehow or just rely on form
                    container.appendChild(box);
                    box.querySelector('.remove-img').onclick = () => box.remove();
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // Category dynamic fields
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', toggleCategoryFields);
        toggleCategoryFields();
    }
});

function toggleCategoryFields() {
    const cat = document.getElementById('category_id');
    if (!cat) return;
    const val = cat.options[cat.selectedIndex]?.text || '';
    document.querySelectorAll('.cat-fields').forEach(el => el.style.display = 'none');
    if (val.includes('Individual House') || val.includes('Villa')) {
        document.querySelectorAll('.house-villa-fields').forEach(el => el.style.display = 'block');
    }
    if (val.includes('Apartment') || val.includes('Residential')) {
        document.querySelectorAll('.apt-fields').forEach(el => el.style.display = 'block');
    }
    if (val.includes('Architectural')) {
        document.querySelectorAll('.arch-fields').forEach(el => el.style.display = 'block');
    }
    if (val.includes('Interior')) {
        document.querySelectorAll('.interior-fields').forEach(el => el.style.display = 'block');
    }
}
