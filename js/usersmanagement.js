// Global variable for current user ID
let currentUserId = null;

$(document).ready(function () {
    const table = $('#usersTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
    });
    
    // Attach event listeners to edit buttons using event delegation
    // This ensures they work even after DataTable redraws the table
    $(document).on('click', '.edit-btn', function () {
        const row = $(this).closest('tr');
        const viewBtn = row.find('.view-btn');
        const userData = viewBtn.attr('data-usedata');
        
        if (!userData) {
            alert('Error: Could not find user data');
            console.error('data-usedata attribute not found');
            return;
        }
        
        try {
            const user = JSON.parse(userData);
            const userId = user.id;
            
            console.log('Edit button clicked for user:', userId);
            
            // Callback to attach save button listener AFTER content loads
            const attachSaveListener = function() {
                const saveBtn = document.getElementById('editSaveBtn');
                if (saveBtn) {
                    saveBtn.removeEventListener('click', handleSaveClick);
                    saveBtn.addEventListener('click', handleSaveClick);
                    console.log('Save button listener attached');
                }
            };
            
            loadModalContent('editaccount', userId, 'editAccountModal', 'editModalContent', attachSaveListener);
        } catch (error) {
            console.error('Error parsing user data:', error);
            alert('Error loading user data');
        }
    });
});

// Reusable function to load modal content from PHP files
function loadModalContent(filePath, userId, modalId, contentId, callback) {
    // Remove .php extension if present to use clean URLs (respects .htaccess rewrite rules)
    const cleanPath = filePath.endsWith('.php') ? filePath.slice(0, -4) : filePath;
    const url = `/MMBPOS/reusablepage/${cleanPath}?id=${userId}`;
    
    console.log('Loading modal content from:', url);
    
    fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error('HTTP error, status: ' + res.status);
            }
            return res.text();
        })
        .then(html => {
            const modalContent = document.getElementById(contentId);
            if (!modalContent) {
                console.error('Modal content element not found with ID:', contentId);
                alert('Error: Modal container not found');
                return;
            }
            
            modalContent.innerHTML = html;
            console.log('HTML inserted into modal');
            
            // Execute callback after content is loaded (e.g., attach event listeners)
            if (callback && typeof callback === 'function') {
                console.log('Executing callback function');
                callback();
            }
            
            // Show the modal
            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                console.error('Modal element not found with ID:', modalId);
                alert('Error: Modal not found');
                return;
            }
            
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal shown');
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading content: ' + error.message);
        });
}

// Show User Details Modal - Load from viewaccount.php
function showUserDetails(button) {
    const user = JSON.parse(button.getAttribute('data-usedata'));
    const userId = user.id;
    
    loadModalContent('viewaccount', userId, 'userDetailsModal', 'viewModalContent');
}

// Open Edit Modal from View modal
function openEditFromView(userId) {
    // Close the view modal
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('userDetailsModal'));
    if (viewModal) {
        viewModal.hide();
    }
    
    // Callback to attach save button listener AFTER content loads
    const attachSaveListener = function() {
        const saveBtn = document.getElementById('editSaveBtn');
        if (saveBtn) {
            saveBtn.removeEventListener('click', handleSaveClick);
            saveBtn.addEventListener('click', handleSaveClick);
            console.log('Save button listener attached');
        }
    };
    
    // Load edit modal with the user ID
    loadModalContent('editaccount', userId, 'editAccountModal', 'editModalContent', attachSaveListener);
}

// Open Add Account Modal
document.getElementById('addAccountBtn').addEventListener('click', function() {
    // Load add account form without needing an ID - use clean URL (no .php)
    fetch(`/MMBPOS/reusablepage/addaccount`)
        .then(res => res.text())
        .then(html => {
            const modalContent = document.getElementById('addModalContent');
            if (modalContent) {
                modalContent.innerHTML = html;
                
                // Bind save button event after modal is loaded
                const saveBtn = document.getElementById('addSaveBtn');
                if (saveBtn) {
                    saveBtn.addEventListener('click', handleAddSaveClick);
                }
            }
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
            modal.show();
        })
        .catch(error => {
            alert('Error loading form: ' + error.message);
        });
});

// Handle add account save button click
function handleAddSaveClick() {
    const form = document.getElementById('addAccountForm');
    if (!form) return;
    
    console.log('Form found:', form);
    console.log('Form method:', form.method);
    
    const formData = new FormData(form);
    
    // Debug: Log what data is being sent
    console.log('FormData entries:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    fetch('/MMBPOS/reusablepage/addaccount', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        // Check if response is okay
        if (!res.ok) {
            throw new Error('Server error: HTTP ' + res.status);
        }
        return res.text(); // Get raw text first
    })
    .then(text => {
        console.log('Raw response from server:', text);
        console.log('Response length:', text.length);
        console.log('First 100 chars:', text.substring(0, 100));
        
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            const alertBox = document.getElementById('addAlert');
            if (!alertBox) return;
            
            alertBox.classList.remove('d-none');
            
            if (data.success) {
                alertBox.className = 'alert alert-success alert-dismissible fade show';
                alertBox.innerHTML = `<i class="fas fa-check-circle me-2"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                setTimeout(() => location.reload(), 1500);
            } else {
                alertBox.className = 'alert alert-danger alert-dismissible fade show';
                alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            }
        } catch (parseError) {
            // If JSON parsing fails, show raw response
            console.error('JSON Parse Error:', parseError);
            console.error('Raw text that failed to parse:', text);
            
            const alertBox = document.getElementById('addAlert');
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger alert-dismissible fade show';
                
                // Show a preview of the response
                let preview = text.substring(0, 100);
                if (text.length > 100) preview += '...';
                
                alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i><strong>Server Error:</strong> Received HTML instead of JSON. This means the POST handler didn't execute. <small>(Check browser F12 console for details)</small><button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            }
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        const alertBox = document.getElementById('addAlert');
        if (alertBox) {
            alertBox.classList.remove('d-none');
            alertBox.className = 'alert alert-danger alert-dismissible fade show';
            alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Network Error: ${error.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        }
    });
}

// Handle edit account save button click
function handleSaveClick() {
    const form = document.getElementById('editAccountForm');
    if (!form) return;
    
    console.log('Edit form found:', form);
    
    const formData = new FormData(form);
    
    // Debug: Log what data is being sent
    console.log('FormData entries:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    fetch('/MMBPOS/reusablepage/editaccount', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log('Response status:', res.status);
        if (!res.ok) {
            throw new Error('Server error: HTTP ' + res.status);
        }
        return res.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            const alertBox = document.getElementById('editAlert');
            if (!alertBox) return;
            
            alertBox.classList.remove('d-none');
            
            if (data.success) {
                alertBox.className = 'alert alert-success alert-dismissible fade show';
                alertBox.innerHTML = `<i class="fas fa-check-circle me-2"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                setTimeout(() => location.reload(), 1500);
            } else {
                alertBox.className = 'alert alert-danger alert-dismissible fade show';
                alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            }
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            console.error('Response was:', text);
            
            const alertBox = document.getElementById('editAlert');
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger alert-dismissible fade show';
                alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Error parsing server response. Check browser console (F12).<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alertBox = document.getElementById('editAlert');
        if (alertBox) {
            alertBox.classList.remove('d-none');
            alertBox.className = 'alert alert-danger alert-dismissible fade show';
            alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Network Error: ${error.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        }
    });
}

// Edit button event listeners are attached via event delegation in document.ready() above
