console.log('Service Worker: Loaded');

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    // Default URL to focus/open
    const targetUrl = event.notification.data?.link || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // Check if there is already a window open with the same domain
            // and focus it instead of opening a new one
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                // Focus any tab that is on the same site and has the target path
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            // If no tab is open with the specific URL, but at least one is open on the site
            if (clientList.length > 0 && 'focus' in clientList[0]) {
                clientList[0].navigate(targetUrl);
                return clientList[0].focus();
            }
            // If no tabs are open at all, open a new one
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
