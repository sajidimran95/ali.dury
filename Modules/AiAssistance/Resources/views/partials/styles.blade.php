<style>
.ai-shell {
    --ai-purple: #7c3aed;
    --ai-purple-soft: #f3e8ff;
    --ai-border: #e5e7eb;
    --ai-muted: #6b7280;
    --ai-bg: #f8fafc;
    --ai-ink: #111827;
    font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    letter-spacing: -0.011em;
    color: #3f4858;
}
.ai-panel {
    background: #fff;
    border: 1px solid var(--ai-border);
    border-radius: 20px;
    box-shadow: 0 24px 64px -16px rgba(15, 23, 42, 0.25), 0 2px 6px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 120px);
}
.ai-panel__head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid #f0eef7;
    background:
        radial-gradient(600px 120px at 0% 0%, rgba(167, 139, 250, 0.14), transparent 70%),
        linear-gradient(180deg, #faf8ff 0%, #fff 100%);
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.ai-panel__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 55%, #5b21b6 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
    box-shadow: 0 8px 20px -8px rgba(124, 58, 237, 0.65), inset 0 1px 0 rgba(255,255,255,.25);
}
.ai-panel__title {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: var(--ai-ink, #111827);
    line-height: 1.2;
    letter-spacing: -0.03em;
}
.ai-panel__sub {
    margin: 4px 0 0;
    color: var(--ai-muted);
    font-size: 13px;
    font-weight: 500;
    line-height: 1.45;
    letter-spacing: -0.01em;
}
.ai-panel__actions {
    margin-left: auto;
    display: flex;
    gap: 8px;
    align-items: center;
}
.ai-panel__close,
.ai-panel__link {
    border: 0;
    background: transparent;
    color: #9ca3af;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
}
.ai-panel__close:hover,
.ai-panel__link:hover { background: #f3f4f6; color: #374151; }

.ai-tabs {
    display: inline-flex;
    gap: 4px;
    background: #f3f4f6;
    padding: 4px;
    border-radius: 999px;
    margin: 12px 20px 0;
    width: fit-content;
}
.ai-tabs a,
.ai-tabs button.ai-tab-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 0;
    background: transparent;
    color: #4b5563 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.ai-tabs a.active,
.ai-tabs button.ai-tab-link.active {
    background: #fff;
    color: #111827 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}

.ai-panel__body {
    padding: 16px 20px 20px;
    overflow: auto;
    flex: 1;
}

.ai-insights-voice .ai-insights-speak-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.ai-insights-voice .ai-insights-speak-btn[data-speaking="1"] {
    border-color: #dc2626;
    color: #dc2626;
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.ai-alerts {
    border: 1px solid #fde2e2;
    background: linear-gradient(180deg, #fffafa 0%, #fff 100%);
    border-radius: 14px;
    padding: 12px 14px;
    margin: 0 0 14px;
}
.ai-alerts__head {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.ai-alerts__title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #b91c1c;
}
.ai-alerts__count,
.ai-alert-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
}
.ai-alerts__count[hidden],
.ai-alert-badge[hidden] { display: none; }
.ai-alert-badge--fab {
    position: absolute;
    top: -6px;
    right: -6px;
    box-shadow: 0 0 0 2px #f4f6fa;
}
.ai-ask-fab__icon { position: relative; }
.ai-alerts__tools {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ai-alerts__voice {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ai-alerts__voice input { margin: 0; }
.ai-alerts__btn {
    border: 1px solid var(--ai-border);
    background: #fff;
    color: #374151;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}
.ai-alerts__btn:hover { background: #f9fafb; }
.ai-alerts__empty {
    margin: 0;
    font-size: 12px;
    color: var(--ai-muted);
}
.ai-alerts__list {
    max-height: 260px;
    overflow: auto;
}
.ai-alert {
    display: flex;
    gap: 10px;
    padding: 9px 4px;
    border-top: 1px solid #f3f4f6;
    text-decoration: none !important;
    color: inherit !important;
}
.ai-alert:first-child { border-top: 0; }
a.ai-alert:hover { background: #fafafa; border-radius: 8px; }
.ai-alert__icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 13px;
}
.ai-alert--po_cost .ai-alert__icon { background: #fef3c7; color: #b45309; }
.ai-alert--invoice .ai-alert__icon { background: #fee2e2; color: #b91c1c; }
.ai-alert--price_edit .ai-alert__icon { background: var(--ai-purple-soft); color: #6d28d9; }
.ai-alert__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ai-alert__title { font-size: 13px; font-weight: 700; color: #111827; }
.ai-alert.is-unread .ai-alert__title::before {
    content: '';
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ef4444;
    margin-right: 6px;
    vertical-align: middle;
}
.ai-alert__msg {
    font-size: 12px;
    color: #4b5563;
    white-space: pre-line;
    line-height: 1.45;
}
.ai-alert__time { font-size: 11px; color: #9ca3af; }

.ai-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
@media (max-width: 992px) {
    .ai-cards { grid-template-columns: 1fr; }
}
.ai-card {
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    padding: 14px;
    background: #fff;
}
.ai-card__label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--ai-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.ai-card__value {
    font-size: 17px;
    font-weight: 800;
    color: var(--ai-ink, #111827);
    margin-bottom: 8px;
    line-height: 1.35;
    letter-spacing: -0.025em;
}
.ai-card ul {
    margin: 0;
    padding-left: 18px;
    color: #4b5563;
    font-size: 13px;
    font-weight: 500;
}
.ai-card li { margin-bottom: 4px; }

.ai-actions-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 18px 0 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #374151;
}
.ai-actions-head .count {
    margin-left: auto;
    color: var(--ai-muted);
}
.ai-action {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 8px;
    text-decoration: none !important;
    color: inherit !important;
    background: #fff;
}
.ai-action:hover { background: #fafafa; }
.ai-action__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
}
.ai-action__dot.high { background: #e11d48; }
.ai-action__dot.medium { background: #f59e0b; }
.ai-action__title {
    font-weight: 700;
    font-size: 14px;
    color: #111827;
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.ai-badge {
    font-size: 11px;
    font-weight: 700;
    border-radius: 999px;
    padding: 2px 8px;
}
.ai-badge.high { background: #ffe4e6; color: #be123c; }
.ai-badge.medium { background: #ffedd5; color: #c2410c; }
.ai-action__desc {
    margin-top: 4px;
    color: var(--ai-muted);
    font-size: 13px;
}

.ai-chat {
    display: flex;
    flex-direction: column;
    min-height: 420px;
    height: 100%;
}
.ai-chat__msgs {
    flex: 1;
    overflow: auto;
    padding: 4px 0 12px;
}
.ai-msg {
    display: flex;
    gap: 10px;
    margin-bottom: 14px;
    align-items: flex-start;
}
.ai-msg.user { flex-direction: row-reverse; }
.ai-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #4b5563;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
}
.ai-msg.user .ai-avatar { background: #2563eb; color: #fff; }
.ai-msg.bot .ai-avatar {
    background: linear-gradient(145deg, #7c3aed 0%, #5b21b6 100%);
    color: #fff;
    box-shadow: 0 2px 6px rgba(124, 58, 237, 0.35);
}
.ai-msg.bot .ai-avatar .japsai-icon {
    display: block;
    width: 15px;
    height: 15px;
}
.ai-panel__icon .japsai-icon {
    display: block;
}
.ai-bubble {
    max-width: 85%;
    padding: 11px 14px;
    border-radius: 4px 16px 16px 16px;
    background: #f7f7fb;
    border: 1px solid #eeedf5;
    color: #111827;
    font-size: 14px;
    line-height: 1.5;
    white-space: pre-wrap;
}
.ai-msg.user .ai-bubble {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    border-color: transparent;
    border-radius: 16px 4px 16px 16px;
    color: #fff;
    box-shadow: 0 6px 16px -8px rgba(109, 40, 217, 0.55);
}
.ai-tool {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    margin: 0 0 10px 38px;
}
.ai-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.ai-chip {
    border: 1px solid #ebe7f7;
    background: #fbfaff;
    color: #4b5563;
    border-radius: 999px;
    padding: 7px 13px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}
.ai-chip:hover, .ai-chip.active {
    background: var(--ai-purple-soft);
    border-color: #d8b4fe;
    color: #6b21a8;
    transform: translateY(-1px);
}
button.ai-intel-btn,
a.ai-intel-btn,
a.ai-intel-link {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--ai-border);
    background: #fff;
    color: #374151 !important;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none !important;
    cursor: pointer;
}
button.ai-intel-btn:hover,
button.ai-intel-btn.is-active,
a.ai-intel-btn:hover,
a.ai-intel-btn.is-active,
a.ai-intel-link:hover,
a.ai-intel-link:focus {
    background: var(--ai-purple-soft);
    border-color: #d8b4fe;
    color: #6b21a8 !important;
}
.ai-intel-report {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--ai-border);
    font-size: 13px;
}
.ai-intel-report table {
    font-size: 12px;
}
button.japs-intel-chip {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--ai-border);
    background: #fff;
    color: #374151;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
button.japs-intel-chip:hover,
button.japs-intel-chip.active {
    background: var(--ai-purple-soft);
    border-color: #d8b4fe;
    color: #6b21a8;
}
.ai-modal #ai-pane-intel {
    height: 100%;
    min-height: 0;
}
.ai-modal #ai-intel-chips {
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 2;
    background: #fff;
    padding-bottom: 8px;
}
.ai-modal #ai-intel-report {
    max-height: calc(100vh - 320px);
    min-height: 200px;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 16px;
}
.ai-modal #ai-intel-report table {
    width: max-content;
    min-width: 100%;
}
.ai-chat__footer {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 6px 6px 6px 10px;
    background: #fff;
    border: 1px solid #e4e0f5;
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px -12px rgba(124, 58, 237, 0.25);
    transition: border-color .15s ease, box-shadow .15s ease;
}
.ai-chat__footer:focus-within {
    border-color: #c4b5fd;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12), 0 8px 24px -12px rgba(124, 58, 237, 0.35);
}
.ai-chat__footer .ai-attach-btn {
    color: #9ca3af;
    height: 36px;
    border-radius: 10px;
    transition: color .15s ease, background .15s ease;
}
.ai-chat__footer .ai-attach-btn:hover { color: var(--ai-purple); background: var(--ai-purple-soft); }
.ai-shell #ai-chat-input,
.ai-chat__footer #ai-chat-input {
    flex: 1;
    min-width: 0;
    border: 0 !important;
    outline: none !important;
    background: transparent !important;
    background-color: transparent !important;
    color: var(--ai-ink) !important;
    -webkit-text-fill-color: var(--ai-ink);
    caret-color: var(--ai-purple);
    border-radius: 10px;
    height: 40px;
    padding: 0 8px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: none !important;
}
.ai-shell #ai-chat-input::placeholder {
    color: #9ca3af !important;
    -webkit-text-fill-color: #9ca3af;
    opacity: 1;
}
.ai-send {
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: 0 6px 16px -6px rgba(109, 40, 217, 0.6);
    transition: transform .15s ease, box-shadow .15s ease;
}
.ai-send:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px -8px rgba(109, 40, 217, 0.7);
}
.ai-send:disabled,
.ai-send.is-busy,
.ai-chip:disabled {
    opacity: .55;
    cursor: wait;
    pointer-events: none;
}
.ai-chat__footer #ai-chat-input:disabled {
    opacity: .75;
    cursor: wait;
}
/* While modal open, ensure chat input is never covered by leftover loaders */
body.ai-modal-open {
    overflow: hidden !important;
}
html.ai-modal-open {
    overflow: hidden !important;
}
body.ai-modal-open .pace,
body.ai-modal-open .pace-active {
    pointer-events: none !important;
}
body.ai-modal-open #ai-modal,
body.ai-modal-open #ai-modal .ai-chat__footer,
body.ai-modal-open #ai-chat-input,
body.ai-modal-open .ai-send {
    pointer-events: auto !important;
}
.ai-modal,
.ai-modal.open {
    pointer-events: auto;
}

/* Modal overlay from FAB */
.ai-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .4);
    -webkit-backdrop-filter: blur(4px);
    backdrop-filter: blur(4px);
    z-index: 1060;
    display: none;
}
.ai-modal-backdrop.open { display: block; }
.ai-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(1040px, calc(100vw - 32px));
    height: calc(100vh - 24px);
    max-height: calc(100vh - 24px);
    z-index: 1070;
    display: none;
}
.ai-modal.open { display: flex; flex-direction: column; }
.ai-modal #ai-modal-content {
    flex: 1;
    min-height: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.ai-modal .ai-shell,
.ai-modal .ai-panel {
    height: 100%;
    max-height: none;
    min-height: 0;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.ai-modal .ai-panel__body {
    flex: 1;
    min-height: 0;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
}
.ai-modal .ai-tab-pane {
    min-height: 0;
}
.ai-modal .ai-chat {
    min-height: 0;
    height: 100%;
    flex: 1;
}
/* Sidebar "Boost with AI" card — matches left-nav promo placement */
.japs-sidebar-ai-slot:empty {
    display: none;
}
.japs-sidebar-ai-slot {
    margin: 0 0 8px;
}
.ai-ask-fab {
    position: static !important;
    top: auto !important;
    left: auto !important;
    right: auto !important;
    bottom: auto !important;
    z-index: 1;
    display: none !important; /* hidden until mounted in sidebar */
    align-items: flex-start;
    gap: 10px;
    width: 100%;
    padding: 12px 12px 13px;
    border-radius: 14px;
    color: #1f2937 !important;
    text-decoration: none !important;
    text-align: left;
    font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
    border: 1px solid #e8ecf3;
    background: #f4f6fa;
    box-shadow: none;
    cursor: pointer;
    -webkit-font-smoothing: antialiased;
    line-height: 1.25;
    height: auto;
    min-height: 0;
    box-sizing: border-box;
    margin: 0 !important;
    transform: none !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.japs-sidebar-ai-slot .ai-ask-fab {
    display: flex !important;
}
.ai-ask-fab:hover {
    background: #eef2f8 !important;
    border-color: #d5deee;
    color: #111827 !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}
.ai-ask-fab__icon {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}
.ai-ask-fab__text {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}
.ai-ask-fab__title {
    display: block;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #1f2937;
    line-height: 1.25;
}
.ai-ask-fab__desc {
    display: block;
    font-size: 11px;
    font-weight: 500;
    line-height: 1.35;
    color: #6b7280;
    white-space: normal;
}
.ai-ask-fab:hover .ai-ask-fab__title {
    color: #111827;
}
.ai-ask-fab:hover .ai-ask-fab__desc {
    color: #4b5563;
}

/* Collapsed sidebar: icon only */
body.zoho-theme .side-bar.zoho-sidebar-collapsed .ai-ask-fab,
body.zoho-theme.sidebar-collapse .side-bar .ai-ask-fab {
    justify-content: center;
    align-items: center;
    padding: 10px;
}
body.zoho-theme .side-bar.zoho-sidebar-collapsed .ai-ask-fab__text,
body.zoho-theme.sidebar-collapse .side-bar .ai-ask-fab__text {
    display: none !important;
}

@media print {
    .ai-ask-fab, .ai-modal, .ai-modal-backdrop, .japs-sidebar-ai-slot { display: none !important; }
}
</style>
