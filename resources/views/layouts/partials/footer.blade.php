<div class="japs-page-footer no-print" id="japs-page-footer">
  <div class="japs-page-footer__inner">
    <p class="japs-page-footer__copy tw-mb-0">
      {{ config('app.name', 'Japstech POS') }} | Copyright &copy; {{ date('Y') }} All rights reserved.
    </p>
  </div>
</div>

<style>
#scrollable-container {
  display: flex !important;
  flex-direction: column !important;
}
/* Contain Bootstrap floats so the footer cannot climb into mid-page gaps */
#scrollable-container > .content,
#scrollable-container > .content-header,
#scrollable-container > section {
  flex: 0 0 auto;
  width: 100%;
  display: flow-root;
}
.japs-page-footer {
  margin-top: auto;
  flex-shrink: 0;
  width: 100%;
  position: relative;
  z-index: 2;
  background: #f7f9fc;
  border-top: 1px solid #e2e8f0;
}
.japs-page-footer__inner {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  min-height: 40px;
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
@media print {
  .japs-page-footer { display: none !important; }
}
</style>
