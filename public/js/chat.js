/**
 * Initializes the chat functionality.
 * @param {Object} config - Configuration object containing necessary URLs and tokens.
 * @param {string} config.mercureUrl - URL for the Mercure hub.
 * @param {string} config.sendMessageUrl - URL for sending messages.
 * @param {string} config.csrfToken - CSRF token for secure message sending.
 */
function initChat(config) {
    const chatMessages = document.getElementById("chat-messages");
    const messageForm = document.getElementById("message-form");
    const messageInput = document.getElementById("message-input");

    let eventSource;

    /**
     * Establishes a connection to the Mercure hub and sets up event listeners.
     */
    function connectToMercure() {
        eventSource = new EventSource(config.mercureUrl, { withCredentials: true });

        eventSource.onopen = () => {};

        eventSource.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);
                addMessageToChat(message);
            } catch (error) {
                // Error handling for message parsing
            }
        };

        eventSource.onerror = () => {
            eventSource.close();
            setTimeout(connectToMercure, 5000);
        };
    }

    /**
     * Adds a new message to the chat display.
     * @param {Object} message - The message object to be added.
     * @param {string} message.user - The username of the message sender.
     * @param {string} message.datetime - The timestamp of the message.
     * @param {string} message.content - The content of the message.
     */
    function addMessageToChat(message) {
        const messageElement = document.createElement("div");
        messageElement.classList.add("message");
        messageElement.innerHTML = `
            <strong>${message.user}</strong>
            <span>${message.datetime}</span>
            <p>${message.content}</p>
        `;
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Initialize the Mercure connection
    connectToMercure();

    // Set up the message form submission handler
    messageForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const content = messageInput.value.trim();
        if (!content) return;

        try {
            const response = await fetch(config.sendMessageUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": config.csrfToken,
                },
                body: JSON.stringify({ content: content }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || "Failed to send message");
            }

            await response.json();
            messageInput.value = "";
        } catch (error) {
            console.log("Error sending message:", error);
            
        }
    });
}
