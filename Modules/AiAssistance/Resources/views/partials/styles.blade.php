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
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 120px);
}
.ai-panel__head {
    padding: 18px 20px 12px;
    border-bottom: 1px solid var(--ai-border);
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.ai-panel__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--ai-purple);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
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
.ai-tabs a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    color: #4b5563 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
}
.ai-tabs a.active {
    background: #fff;
    color: #111827 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}

.ai-panel__body {
    padding: 16px 20px 20px;
    overflow: auto;
    flex: 1;
}

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
.ai-msg.bot .ai-avatar { background: #eef2ff; color: var(--ai-purple); }
.ai-bubble {
    max-width: 85%;
    padding: 10px 12px;
    border-radius: 14px;
    background: #f3f4f6;
    color: #111827;
    font-size: 14px;
    line-height: 1.45;
    white-space: pre-wrap;
}
.ai-msg.user .ai-bubble {
    background: var(--ai-purple);
    color: #fff;
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
    border: 1px solid var(--ai-border);
    background: #fff;
    color: #374151;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.ai-chip:hover, .ai-chip.active {
    background: var(--ai-purple-soft);
    border-color: #d8b4fe;
    color: #6b21a8;
}
.ai-chat__footer {
    border-top: 1px solid var(--ai-border);
    padding-top: 12px;
    display: flex;
    gap: 8px;
    align-items: center;
}
.ai-chat__footer input {
    flex: 1;
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    height: 44px;
    padding: 0 14px;
    box-shadow: none;
}
.ai-send {
    width: 44px;
    height: 44px;
    border: 0;
    border-radius: 12px;
    background: var(--ai-purple);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ai-send:disabled { opacity: .5; }

/* Modal overlay from FAB */
.ai-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .45);
    z-index: 1060;
    display: none;
}
.ai-modal-backdrop.open { display: block; }
.ai-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(920px, calc(100vw - 32px));
    max-height: calc(100vh - 48px);
    z-index: 1070;
    display: none;
}
.ai-modal.open { display: block; }
.ai-modal .ai-panel { max-height: calc(100vh - 48px); }
.ai-ask-fab {
    position: fixed !important;
    top: auto !important;
    left: auto !important;
    right: 78px !important;
    bottom: 24px !important;
    z-index: 1050;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 999px;
    color: #fff !important;
    text-decoration: none !important;
    font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: -0.02em;
    border: 0;
    background: linear-gradient(135deg, #7c3aed, #2563eb);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
    cursor: pointer;
    -webkit-font-smoothing: antialiased;
    white-space: nowrap;
    line-height: 1.2;
    height: auto;
    min-height: 44px;
    box-sizing: border-box;
    margin: 0 !important;
    transform: none !important;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.ai-ask-fab.is-visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.ai-ask-fab:hover { filter: brightness(1.05); color: #fff !important; }
.ai-ask-fab i {
    font-size: 14px;
    line-height: 1;
}
@media print {
    .ai-ask-fab, .ai-modal, .ai-modal-backdrop { display: none !important; }
}
</style>
