document.addEventListener('DOMContentLoaded', function() {

    // Toggle Active Status
    document.querySelectorAll('.toggle-active').forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const isActive = this.checked ? 1 : 0;

            fetch('toggle_active.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ id: userId, active: isActive }).toString()
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() !== 'success') {
                    alert('There was an issue updating the status.');
                    this.checked = !this.checked; // revert switch if error
                }
            })
            .catch(error => {
                alert('There was an issue updating the status.');
                this.checked = !this.checked; // revert switch if error
            });
        });
    });

    // Add User
    document.getElementById('add-user-btn').addEventListener('click', function() {
        Swal.fire({
            title: 'Add New User',
            html: `
                <input id="add-username" class="swal2-input" placeholder="Username">
                <input id="add-first-name" class="swal2-input" placeholder="First Name">
                <input id="add-last-name" class="swal2-input" placeholder="Last Name">
                <input id="add-password" type="password" class="swal2-input" placeholder="Password">
                <select id="add-role" class="swal2-select">
                    <option value="User">User</option>
                    <option value="Admin">Admin</option>
                    <!-- Add other roles as needed -->
                </select>
            `,
            focusConfirm: false,
            preConfirm: () => {
                const username = Swal.getPopup().querySelector('#add-username').value.trim();
                const firstName = Swal.getPopup().querySelector('#add-first-name').value.trim();
                const lastName = Swal.getPopup().querySelector('#add-last-name').value.trim();
                const password = Swal.getPopup().querySelector('#add-password').value.trim();
                const role = Swal.getPopup().querySelector('#add-role').value;

                if (!username || !firstName || !lastName || !password || !role) {
                    Swal.showValidationMessage('Please fill in all fields.');
                    return false;
                }

                return { username, firstName, lastName, password, role };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('add_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(result.value).toString()
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'User Added',
                        text: data
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'There was an error adding the user.'
                    });
                });
            }
        });
    });
    function searchUsers() {
        const query = document.getElementById('search-box').value.toLowerCase();
        console.log('Search Query:', query); // Add this line for debugging
        const rows = document.querySelectorAll('table tbody tr');
    
        rows.forEach(row => {
            const cells = row.getElementsByTagName('td');
            let match = false;
    
            for (let i = 0; i < cells.length; i++) {
                if (cells[i].textContent.toLowerCase().includes(query)) {
                    match = true;
                    break;
                }
            }
    
            console.log('Row Match:', match); // Add this line for debugging
            row.style.display = match ? '' : 'none';
        });
    }
    

    document.getElementById('search-box').addEventListener('keyup', searchUsers);
    // Edit User
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const firstName = this.getAttribute('data-first-name');
            const lastName = this.getAttribute('data-last-name');
            const username = this.getAttribute('data-username');
            const role = this.getAttribute('data-role');
    
            Swal.fire({
                title: 'Edit User',
                html: `
                    <input id="edit-username" class="swal2-input" placeholder="Username" value="${username}">                
                    <input id="edit-first-name" class="swal2-input" placeholder="First Name" value="${firstName}">
                    <input id="edit-last-name" class="swal2-input" placeholder="Last Name" value="${lastName}">
                    <input id="edit-password" type="password" class="swal2-input" placeholder="New Password">
                    <br>
                    <small class="swal2-description">* หากไม่ต้องการเปลี่ยนรหัสผ่าน กรุณาเว้นว่างไว้</small>
                    <br>
                    <select id="edit-role" class="swal2-select">
                        <option value="User" ${role === 'User' ? 'selected' : ''}>User</option>
                        <option value="Admin" ${role === 'Admin' ? 'selected' : ''}>Admin</option>
                        <!-- Add other roles as needed -->
                    </select>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const newUsername = Swal.getPopup().querySelector('#edit-username').value.trim();
                    const newFirstName = Swal.getPopup().querySelector('#edit-first-name').value.trim();
                    const newLastName = Swal.getPopup().querySelector('#edit-last-name').value.trim();
                    const newPassword = Swal.getPopup().querySelector('#edit-password').value.trim();
                    const newRole = Swal.getPopup().querySelector('#edit-role').value;
    
                    if (!newFirstName || !newLastName || !newUsername || !newRole) {
                        Swal.showValidationMessage('กรุณากรอกชื่อจริง, นามสกุล, ชื่อผู้ใช้, และเลือกบทบาท');
                        return false;
                    }
    
                    return { userId, newUsername, newFirstName, newLastName, newPassword, newRole };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('edit_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(result.value).toString()
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'อัปเดตผู้ใช้สำเร็จ',
                                text: data.message
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'เกิดข้อผิดพลาดในการอัปเดตผู้ใช้'
                        });
                    });
                }
            });
        });
    });
    
    
    
});
