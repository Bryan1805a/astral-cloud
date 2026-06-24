<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="footer-logo">A</div>
            <h3>Astral Cloud</h3>
            <p><?= __('footer_tagline') ?></p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4><?= __('footer_platform') ?></h4>
                <a href="/plans"><?= __('nav_plans') ?></a>
                <a href="/docs"><?= __('nav_docs') ?></a>
                <a href="/blog"><?= __('nav_blog') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('footer_account') ?></h4>
                <a href="/login"><?= __('nav_login') ?></a>
                <a href="/register"><?= __('nav_register') ?></a>
                <a href="/orders"><?= __('nav_orders') ?></a>
                <a href="/profile"><?= __('nav_profile') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('footer_support') ?></h4>
                <a href="/docs#faq"><?= __('docs_faq') ?></a>
                <a href="/docs#connect"><?= __('docs_started') ?></a>
                <a href="/docs#commands"><?= __('docs_commands') ?></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= __('footer_copyright') ?></span>
        <span><?= __('footer_powered') ?></span>
    </div>
</footer>

<script src="/js/app.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/app.js') ?>"></script>
<script src="/js/cart.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/cart.js') ?>"></script>

<!-- Chat Support Widget -->
<div id="chat-widget">
    <button id="chat-toggle" title="Chat with us">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </button>
    <div id="chat-box" style="display:none;">
        <div id="chat-header">
            <span>💬 Astral Assistant</span>
            <button id="chat-close">&times;</button>
        </div>
        <div id="chat-messages">
            <div class="chat-msg bot">👋 Hello! I'm the Astral Cloud assistant. How can I help you today?</div>
        </div>
        <div id="chat-suggestions">
            <button class="chat-suggest" data-q="What VPS plans do you have?">What VPS plans do you have?</button>
            <button class="chat-suggest" data-q="How does provisioning work?">How does provisioning work?</button>
            <button class="chat-suggest" data-q="How do I connect to my VPS?">How do I connect to my VPS?</button>
            <button class="chat-suggest" data-q="What payment methods are accepted?">What payment methods are accepted?</button>
        </div>
        <div id="chat-input-row">
            <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off">
            <button id="chat-send">Send</button>
        </div>
    </div>
</div>

<style>
#chat-widget { position:fixed; bottom:20px; right:20px; z-index:9999; font-family:Inter,system-ui,sans-serif; }
#chat-toggle { width:54px; height:54px; border-radius:50%; background:linear-gradient(135deg,#38bdf8,#2563eb); border:none; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 20px rgba(56,189,248,0.3); transition:transform 0.2s; }
#chat-toggle:hover { transform:scale(1.08); }
#chat-box { position:absolute; bottom:64px; right:0; width:340px; height:460px; background:#151515; border:1px solid rgba(255,255,255,0.12); border-radius:18px; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.5); animation:chatSlideIn 0.25s ease; }
@keyframes chatSlideIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
#chat-header { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:rgba(255,255,255,0.04); border-bottom:1px solid rgba(255,255,255,0.06); font-size:14px; font-weight:700; color:#e2e8f0; }
#chat-close { background:none; border:none; color:#6b7280; font-size:22px; cursor:pointer; padding:0; line-height:1; }
#chat-close:hover { color:#fff; }
#chat-messages { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; }
#chat-messages .chat-msg { max-width:85%; padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.5; }
#chat-messages .chat-msg.bot { align-self:flex-start; background:rgba(255,255,255,0.06); color:#e2e8f0; border-bottom-left-radius:4px; }
#chat-messages .chat-msg.user { align-self:flex-end; background:#38bdf8; color:#0f172a; border-bottom-right-radius:4px; }
#chat-suggestions { padding:8px 12px; display:flex; flex-wrap:wrap; gap:6px; border-top:1px solid rgba(255,255,255,0.04); }
.chat-suggest { padding:6px 12px; border-radius:999px; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#94a3b8; font-size:11px; cursor:pointer; transition:all 0.15s; white-space:nowrap; }
.chat-suggest:hover { border-color:#38bdf8; color:#38bdf8; }
#chat-input-row { display:flex; gap:6px; padding:10px 14px; border-top:1px solid rgba(255,255,255,0.06); }
#chat-input { flex:1; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.10); background:rgba(255,255,255,0.05); color:#e2e8f0; font-size:13px; outline:none; font-family:inherit; }
#chat-input:focus { border-color:#38bdf8; }
#chat-send { padding:10px 16px; border-radius:10px; border:none; background:#38bdf8; color:#0f172a; font-size:12px; font-weight:700; cursor:pointer; }
#chat-send:hover { background:#7dd3fc; }
@media(max-width:420px){ #chat-box { width:calc(100vw - 32px); right:0; } }
</style>

<script>
(function(){
    var toggle = document.getElementById('chat-toggle');
    var box = document.getElementById('chat-box');
    var close = document.getElementById('chat-close');
    var messages = document.getElementById('chat-messages');
    var input = document.getElementById('chat-input');
    var send = document.getElementById('chat-send');
    var suggestions = document.getElementById('chat-suggestions');

    var dummyReplies = {
        default: "Thanks for your message! Our team will get back to you shortly. In the meantime, check out our <a href='/docs'>documentation</a> or <a href='/plans'>VPS plans</a>.",
        "plans": "We offer 6 VPS plans ranging from VPS Starter (99,000 VND/mo) to Cloud VM Enterprise (2,999,000 VND/mo). Visit our <a href='/plans'>plans page</a> to compare specs!",
        "provisioning": "After payment, your VPS is cloned from our base Ubuntu template and provisioned automatically. It usually takes 1-3 minutes. Check <a href='/orders'>My Orders</a> for status.",
        "connect": "You can connect via our web terminal (click Console in My Orders) or using any SSH client with the root password shown in your service details.",
        "payment": "We accept payment via VNPay — a secure online payment gateway used across Vietnam. You can pay with ATM cards, credit cards, or e-wallets.",
    };

    function getReply(text) {
        var t = text.toLowerCase();
        if (t.includes('plan') || t.includes('price') || t.includes('vps')) return dummyReplies.plans;
        if (t.includes('provision') || t.includes('clone') || t.includes('create')) return dummyReplies.provisioning;
        if (t.includes('connect') || t.includes('ssh') || t.includes('terminal') || t.includes('console')) return dummyReplies.connect;
        if (t.includes('pay') || t.includes('vnpay') || t.includes('card') || t.includes('method')) return dummyReplies.payment;
        return dummyReplies.default;
    }

    function addMsg(text, cls) {
        var div = document.createElement('div');
        div.className = 'chat-msg ' + cls;
        div.innerHTML = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function sendMsg(text) {
        if (!text.trim()) return;
        addMsg(text, 'user');
        input.value = '';
        setTimeout(function(){
            addMsg(getReply(text), 'bot');
            if (suggestions) suggestions.style.display = 'none';
        }, 600 + Math.random() * 600);
    }

    toggle.addEventListener('click', function(){
        var visible = box.style.display !== 'none';
        box.style.display = visible ? 'none' : 'flex';
    });

    close.addEventListener('click', function(){
        box.style.display = 'none';
    });

    send.addEventListener('click', function(){ sendMsg(input.value); });
    input.addEventListener('keydown', function(e){ if(e.key==='Enter') sendMsg(input.value); });

    if (suggestions) {
        suggestions.querySelectorAll('.chat-suggest').forEach(function(btn){
            btn.addEventListener('click', function(){ sendMsg(btn.getAttribute('data-q')); });
        });
    }
})();
</script>

</body>
</html>
