document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    const forms = document.querySelectorAll('.auth-form, .profile-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input');
            inputs.forEach(input => {
                if(!input.value.trim()) {
                    e.preventDefault();
                    input.style.borderColor = '#e74c3c';
                }
            });
        });
    });
});