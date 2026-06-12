/**
 * Zaza AI chat widget.
 *
 * Floating storefront chat that talks to the n8n order chatbot webhook.
 * Configuration arrives via wp_localize_script as window.zazaChatData
 * (webhookUrl, title, subtitle, greeting); sensible fallbacks keep the
 * widget working if localization is missing.
 *
 * Each request includes the recent conversation history so the bot keeps
 * context across turns (quantities, product under discussion, etc.).
 */
(function () {
	'use strict';

	var cfg = window.zazaChatData || {};
	var webhookUrl = cfg.webhookUrl || 'https://thezazaclub.app.n8n.cloud/webhook/zaza-chat';
	var title = cfg.title || 'The Zaza Club';
	var subtitle = cfg.subtitle || 'Ask about products, orders, and delivery';
	var greeting = cfg.greeting || 'Hey! Welcome to The Zaza Club. How can I help you today?';

	var ink = '#111413';
	var inkSoft = '#262c28';
	var lime = '#b7f16f';
	var cream = '#f7f8f2';
	var bodyFont = '"DM Sans", Arial, Helvetica, sans-serif';

	// Stable conversation id per visitor so the bot can group a session.
	var convoKey = 'zaza_chat_conversation_id';
	var conversationId;
	try {
		conversationId = localStorage.getItem(convoKey);
		if (!conversationId) {
			conversationId = 'web-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
			localStorage.setItem(convoKey, conversationId);
		}
	} catch (e) {
		conversationId = 'web-session-' + Date.now().toString(36);
	}

	var css =
		'.zaza-chat-launcher{position:fixed;bottom:22px;right:22px;width:58px;height:58px;border-radius:50%;border:2px solid ' + lime + ';cursor:pointer;background:' + ink + ';color:' + lime + ';font-size:24px;line-height:1;box-shadow:0 10px 30px rgba(17,20,19,.35);z-index:2147483646;transition:transform .15s ease}' +
		'.zaza-chat-launcher:hover{transform:scale(1.07)}' +
		'.zaza-chat-window{position:fixed;bottom:92px;right:22px;width:360px;max-width:calc(100vw - 28px);height:520px;max-height:calc(100vh - 120px);display:none;flex-direction:column;background:' + cream + ';border:1px solid rgba(17,20,19,.16);border-radius:18px;box-shadow:0 18px 50px rgba(17,20,19,.3);overflow:hidden;z-index:2147483647;font-family:' + bodyFont + '}' +
		'.zaza-chat-window.is-open{display:flex}' +
		'.zaza-chat-window__head{background:' + ink + ';color:' + cream + ';padding:14px 18px}' +
		'.zaza-chat-window__head h3{margin:0;font-size:16px;font-weight:700;color:' + lime + ';font-family:' + bodyFont + '}' +
		'.zaza-chat-window__head p{margin:3px 0 0;font-size:12px;opacity:.8}' +
		'.zaza-chat-window__msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px}' +
		'.zaza-chat-msg{max-width:84%;padding:9px 13px;border-radius:14px;font-size:14px;line-height:1.5;white-space:pre-wrap;overflow-wrap:break-word}' +
		'.zaza-chat-msg--bot{background:#ffffff;color:' + ink + ';border:1px solid rgba(17,20,19,.12);border-bottom-left-radius:4px;align-self:flex-start}' +
		'.zaza-chat-msg--bot a{color:' + ink + ';font-weight:700;text-decoration:underline;overflow-wrap:anywhere}' +
		'.zaza-chat-msg--bot a.zaza-chat-checkout{display:inline-block;margin-top:4px;padding:8px 14px;background:' + lime + ';color:' + ink + ';border-radius:10px;text-decoration:none}' +
		'.zaza-chat-msg--user{background:' + inkSoft + ';color:' + cream + ';border-bottom-right-radius:4px;align-self:flex-end}' +
		'.zaza-chat-msg--typing{color:#626b66;font-style:italic}' +
		'.zaza-chat-window__form{display:flex;border-top:1px solid rgba(17,20,19,.12);background:#ffffff}' +
		'.zaza-chat-window__form input{flex:1;border:none;outline:none;padding:14px;font-size:14px;font-family:' + bodyFont + ';background:transparent;color:' + ink + '}' +
		'.zaza-chat-window__form button{border:none;background:' + lime + ';color:' + ink + ';font-weight:700;font-size:14px;padding:0 18px;cursor:pointer;font-family:' + bodyFont + '}' +
		'.zaza-chat-window__form button:disabled{opacity:.4;cursor:default}' +
		'@media (max-width:480px){.zaza-chat-launcher{bottom:76px;right:14px}.zaza-chat-window{top:72px;bottom:12px;right:10px;left:10px;width:auto;max-width:none;height:auto;max-height:none;border-radius:14px}}';

	function escapeHtml(s) {
		return String(s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function init() {
		if (document.querySelector('.zaza-chat-launcher')) {
			return;
		}

		var style = document.createElement('style');
		style.textContent = css;
		document.head.appendChild(style);

		var launcher = document.createElement('button');
		launcher.type = 'button';
		launcher.className = 'zaza-chat-launcher';
		launcher.setAttribute('aria-label', 'Open chat');
		launcher.textContent = '💬';

		var win = document.createElement('div');
		win.className = 'zaza-chat-window';
		win.setAttribute('role', 'dialog');
		win.setAttribute('aria-label', title + ' chat');

		var head = document.createElement('div');
		head.className = 'zaza-chat-window__head';
		var h3 = document.createElement('h3');
		h3.textContent = title;
		var sub = document.createElement('p');
		sub.textContent = subtitle;
		head.appendChild(h3);
		head.appendChild(sub);

		var msgs = document.createElement('div');
		msgs.className = 'zaza-chat-window__msgs';

		var form = document.createElement('form');
		form.className = 'zaza-chat-window__form';
		var input = document.createElement('input');
		input.type = 'text';
		input.maxLength = 1000;
		input.placeholder = 'Type your message...';
		var send = document.createElement('button');
		send.type = 'submit';
		send.textContent = 'Send';
		form.appendChild(input);
		form.appendChild(send);

		win.appendChild(head);
		win.appendChild(msgs);
		win.appendChild(form);
		document.body.appendChild(launcher);
		document.body.appendChild(win);

		var greeted = false;
		var history = [];

		function remember(role, content) {
			history.push({ role: role, content: String(content).slice(0, 2000) });
			if (history.length > 20) {
				history = history.slice(-20);
			}
		}

		function addMsg(text, variant) {
			var el = document.createElement('div');
			el.className = 'zaza-chat-msg zaza-chat-msg--' + variant;
			if (variant === 'bot') {
				var clean = String(text || '').replace(/\*\*/g, '');
				el.innerHTML = escapeHtml(clean).replace(/(https?:\/\/[^\s<]+[^\s<.,!?')])/g, function (url) {
					if (url.indexOf('add-to-cart') > -1) {
						return '<a class="zaza-chat-checkout" href="' + url + '" target="_blank" rel="noopener">🛒 Tap here to complete your order</a>';
					}
					return '<a href="' + url + '" target="_blank" rel="noopener">' + url + '</a>';
				});
			} else {
				el.textContent = text;
			}
			msgs.appendChild(el);
			msgs.scrollTop = msgs.scrollHeight;
			return el;
		}

		launcher.addEventListener('click', function () {
			win.classList.toggle('is-open');
			if (win.classList.contains('is-open')) {
				if (!greeted) {
					greeted = true;
					addMsg(greeting, 'bot');
					remember('assistant', greeting);
				}
				input.focus();
			}
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var text = input.value.trim();
			if (!text || send.disabled) {
				return;
			}
			addMsg(text, 'user');
			var payload = JSON.stringify({
				conversation_id: conversationId,
				message: text,
				history: history.slice(-12)
			});
			remember('user', text);
			input.value = '';
			send.disabled = true;
			var typing = addMsg('typing...', 'bot');
			typing.classList.add('zaza-chat-msg--typing');

			fetch(webhookUrl, {
				method: 'POST',
				headers: { 'content-type': 'application/json' },
				body: payload
			})
				.then(function (r) {
					if (!r.ok) {
						throw new Error('HTTP ' + r.status);
					}
					return r.json();
				})
				.then(function (data) {
					typing.remove();
					var reply = data.reply || 'Sorry, something went wrong. Please try again.';
					addMsg(reply, 'bot');
					remember('assistant', reply);
				})
				.catch(function () {
					typing.remove();
					addMsg('Sorry, we could not reach the shop assistant right now. Please try again in a moment.', 'bot');
				})
				.then(function () {
					send.disabled = false;
					input.focus();
				});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

