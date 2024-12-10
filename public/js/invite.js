function showNotification(message) {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: absolute;
        top: 2%;
        right: 4%;
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    `;
    document.body.appendChild(notification);

    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);

    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
async function invite(config) {
    try {
        const response = await fetch(`/room/${config.roomId}/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });

        if (!response.ok) {
            throw new Error('Server responded with an error');
        }

        const data = await response.json();
        console.log('Invite link generated:', data.inviteLink);

        // Copy the invite link to clipboard
        await navigator.clipboard.writeText(data.inviteLink);
        console.log('Invite link copied to clipboard');

        // Show a graceful notification
        showNotification('Lien copié dans votre presse papier');
    } catch (error) {
        console.error('Error generating or copying invite link:', error);
        showNotification('Failed to generate or copy invite link. Please try again.');
    }
}
