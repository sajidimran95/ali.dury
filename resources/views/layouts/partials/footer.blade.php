<div class="japs-page-footer no-print" id="japs-page-footer">
  <div class="japs-page-footer__inner">
    <p class="japs-page-footer__copy tw-mb-0">
      {{ config('app.name', 'JAPS POS') }} | Copyright &copy; {{ date('Y') }} All rights reserved.
    </p>
    <div id="japs-footer-ai-slot" class="japs-page-footer__ai"></div>
  </div>
</div>

<style>
/* Keep footer (and Japs AI) at the bottom of the main scroll area */
#scrollable-container {
  display: flex !important;
  flex-direction: column !important;
}
.japs-page-footer {
  position: sticky;
  bottom: 0;
  z-index: 1040;
  margin-top: auto;
  background: rgba(255, 255, 255, 0.96);
  border-top: 1px solid #e2e8f0;
  box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.05);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.japs-page-footer__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 56px;
  padding: 8px 16px;
  padding-left: max(16px, env(safe-area-inset-left));
  padding-right: max(16px, env(safe-area-inset-right));
  padding-bottom: max(8px, env(safe-area-inset-bottom));
}
.japs-page-footer__copy {
  font-size: 12px;
  color: #64748b;
  line-height: 1.3;
}
.japs-page-footer__ai:empty {
  display: none;
}
.japs-page-footer .ai-ask-fab {
  position: static !important;
  top: auto !important;
  right: auto !important;
  bottom: auto !important;
  left: auto !important;
  transform: none !important;
  margin: 0 !important;
  flex-shrink: 0;
}
@media print {
  .japs-page-footer { display: none !important; }
}
@media (max-width: 575px) {
  .japs-page-footer__inner {
    flex-wrap: wrap;
    justify-content: center;
    text-align: center;
  }
}
</style>
