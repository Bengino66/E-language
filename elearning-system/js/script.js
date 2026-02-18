document.addEventListener('DOMContentLoaded', () => {
    console.log('script.js loaded');
    try {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                console.log('Menu toggle clicked');
                const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                menuToggle.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.classList.toggle('hidden');
            });
        } else {
            console.error('Menu toggle or mobile menu not found');
        }
    } catch (error) {
        console.error('Error in menu toggle setup:', error);
    }
});

function validateSignupForm() {
    console.log('Validating signup form');
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const email = document.getElementById('email').value;
    if (username.length < 3) {
        alert('Username must be at least 3 characters long.');
        return false;
    }
    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false;
    }
    if (!email.includes('@') || !email.includes('.')) {
        alert('Please enter a valid email address.');
        return false;
    }
    return true;
}

function validateLoginForm() {
    console.log('Validating login form');
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    if (!username) {
        alert('Please enter a username.');
        return false;
    }
    if (!password) {
        alert('Please enter a password.');
        return false;
    }
    return true;
}

function validateCourseForm() {
    console.log('Validating course form');
    const title = document.querySelector('input[name="title"]').value;
    const description = document.querySelector('textarea[name="description"]').value;
    const category = document.querySelector('select[name="category"]').value;
    if (title.length < 5) {
        alert('Course title must be at least 5 characters long.');
        return false;
    }
    if (description.length < 20) {
        alert('Course description must be at least 20 characters long.');
        return false;
    }
    if (!category) {
        alert('Please select a course category.');
        return false;
    }
    return true;
}

function validateResourceForm() {
    console.log('Validating resource form');
    const courseId = document.querySelector('select[name="course_id"]').value;
    const title = document.querySelector('input[name="title"]').value;
    const resourceType = document.querySelector('select[name="resource_type"]').value;
    const fileInput = document.getElementById('resource-file');
    const urlInput = document.getElementById('resource-url');

    if (!courseId) {
        alert('Please select a course.');
        return false;
    }
    if (title.length < 3) {
        alert('Resource title must be at least 3 characters long.');
        return false;
    }
    if (resourceType === 'url') {
        if (!urlInput.value) {
            alert('Please enter a URL for URL-type resources.');
            return false;
        }
        if (!urlInput.value.match(/^(https?:\/\/)/)) {
            alert('Please enter a valid URL starting with http:// or https://.');
            return false;
        }
    } else {
        if (!fileInput.files[0]) {
            alert('Please upload a file for this resource type.');
            return false;
        }
        const allowedExtensions = {
            document: ['pdf', 'doc', 'docx'],
            image: ['jpg', 'jpeg', 'png'],
            video: ['mp4', 'webm'],
            other: ['txt', 'zip', 'ppt', 'pptx']
        };
        const file = fileInput.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions[resourceType]?.includes(extension) && resourceType !== 'other') {
            alert(`Invalid file type. Allowed for ${resourceType}: ${allowedExtensions[resourceType].join(', ')}`);
            return false;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            return false;
        }
    }
    return true;
}