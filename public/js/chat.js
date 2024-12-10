/**
 * Initializes the chat functionality.
 * @param {Object} config - Configuration object containing necessary URLs and tokens.
 * @param {string} config.mercureHubUrl - URL for the Mercure hub.
 * @param {string} config.sendMessageUrl - URL for sending messages.
 * @param {string} config.csrfToken - CSRF token for secure message sending.
 */
function initChat(config) {
    const chatMessages = document.getElementById("chat-messages");
    const messageForm = document.getElementById("message-form");
    const messageInput = document.getElementById("message-input");

    let eventSource;

    /**
     * Handles the mercure connection and event handling.
     */
    function connectToMercure() {
        const url = new URL(config.mercureHubUrl);
        url.searchParams.append("topic", "room/" + config.roomId);

        eventSource = new EventSource(url, { withCredentials: true });

        eventSource.onopen = () => {
            console.log("Mercure connection opened");
        };

        // Handle incoming messages from the server
        eventSource.onmessage = (event) => {
            console.log("Received Mercure event:", event);
            try {
                const message = JSON.parse(event.data);
                addMessageToChat(message);
            } catch (error) {
                console.error("Error parsing Mercure message:", error);
            }
        };

        // Handle connection errors and reconnection attempts
        eventSource.onerror = (error) => {
            console.error("Mercure connection error:", error);
            eventSource.close();
            console.log("Attempting to reconnect in 5 seconds...");
            setTimeout(connectToMercure, 5000);
        };
    }

    /**
     * Adds a message received from mercure to the chat display.
     * @param {string} message - The message to be added to the chat.
     */
    function addMessageToChat(message) {
        const messageElement = document.createElement("div");
        messageElement.classList.add("message");

        const userElement = document.createElement("strong");
        userElement.textContent = message.user;

        const datetimeElement = document.createElement("span");
        datetimeElement.classList.add("input-field");
        datetimeElement.textContent = message.datetime;

        const contentElement = document.createElement("div");
        contentElement.classList.add("purple");
        contentElement.textContent = message.content;

        messageElement.appendChild(userElement);
        messageElement.appendChild(datetimeElement);
        messageElement.appendChild(contentElement);

        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    connectToMercure();

    /**
     * Submits a new message to the server.
     */
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

            messageInput.value = "";
        } catch (error) {
            console.error("Error sending message:", error);
        }
    });
}
