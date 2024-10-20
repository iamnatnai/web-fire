document.getElementById('edit-profile-form').addEventListener('submit', async function(event) {
    event.preventDefault(); // Prevent default form submission

    const userId = document.getElementById('user-id').value;
    const username = document.getElementById('username').value;
    const firstName = document.getElementById('first-name').value;
    const lastName = document.getElementById('last-name').value;
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;

    try {
        // Send data to the server for validation and update
        const response = await fetch('update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                userId: userId,
                username: username,
                firstName: firstName,
                lastName: lastName,
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire('Success', 'Profile updated successfully', 'success');
            window.location.href = 'index.php'; // Redirect to index page
        } else {
            await Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
    }
});
