/**
 * SLCL - Frontend JavaScript
 * Restricted to Phantom Wallet only.
 */

document.addEventListener('DOMContentLoaded', () => {
    const wrappers = document.querySelectorAll('.slcl-wrapper');
    if (wrappers.length === 0) return;

    wrappers.forEach(wrapper => {
        const mainBtn = wrapper.querySelector('.slcl-main-btn');
        const statusMsg = wrapper.querySelector('.slcl-status-message');

        if (!mainBtn) return;

        mainBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            await connectPhantom(statusMsg);
        });
    });

    async function connectPhantom(statusMsgElement) {
        const provider = window.phantom?.solana || window.solana;
        
        if (!provider || !provider.isPhantom) {
            showMessage(statusMsgElement, 'Phantom wallet not found.', 'error');
            window.open('https://phantom.app/', '_blank');
            return;
        }

        try {
            showMessage(statusMsgElement, 'Connecting to Phantom...', 'info');
            const resp = await provider.connect();
            const publicKeyStr = resp.publicKey.toString();

            const messageText = "Solana Locker Auth.\nNonce: " + slcl_ajax.nonce;
            const encodedMessage = new TextEncoder().encode(messageText);

            showMessage(statusMsgElement, 'Please sign in your wallet.', 'info');

            const signedData = await provider.signMessage(encodedMessage, "utf8");
            const signatureBytes = signedData.signature || signedData; 
            const signatureBase64 = btoa(String.fromCharCode.apply(null, signatureBytes));

            showMessage(statusMsgElement, 'Verifying signature...', 'info');

            const formData = new FormData();
            formData.append('action', 'slcl_verify_wallet');
            formData.append('security', slcl_ajax.nonce);
            formData.append('wallet', publicKeyStr);
            formData.append('signature', signatureBase64);

            const wpResponse = await fetch(slcl_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const result = await wpResponse.json();

            if (result.success) {
                showMessage(statusMsgElement, slcl_ajax.msg_success, 'success');
                setTimeout(() => { window.location.reload(); }, 1000); 
            } else {
                showMessage(statusMsgElement, result.data || slcl_ajax.msg_error, 'error');
            }

        } catch (err) {
            console.error('[SLCL] Auth Error:', err);
            showMessage(statusMsgElement, 'Connection cancelled.', 'error');
            if(provider && typeof provider.disconnect === 'function') {
                provider.disconnect();
            }
        }
    }

    function showMessage(element, text, type) {
        if (!element) return;
        element.style.display = 'block';
        element.innerText = text;
        
        if (type === 'error') {
            element.style.color = '#ff4444';
        } else if (type === 'success') {
            element.style.color = '#00C851';
        } else {
            element.style.color = 'inherit'; 
        }
    }
});