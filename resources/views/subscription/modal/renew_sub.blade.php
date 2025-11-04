<!-- تجديد الاشتراك (Dark Style متناسق مع السيستم) -->
<div id="renewSubscriptionModal" class="custom-modal" aria-hidden="true" style="display:none;">
  <div class="custom-modal-backdrop"></div>

  <div class="custom-modal-dialog" role="dialog" aria-modal="true" tabindex="-1">
    <div class="custom-modal-content">
      <div class="custom-modal-header">
        <h4 class="custom-modal-title">🔄 تجديد الاشتراك</h4>
        <button type="button" id="renewModalClose" class="btn-small">✕</button>
      </div>

      <div class="custom-modal-body">
        <p class="muted">ممكن تختار تغيير الخطة عند التجديد أو تترك الخطة الحالية.</p>

        <form id="renewSubscriptionForm">
          @csrf
          <div class="form-group">
            <label class="form-label">اختر الخطة (اختياري)</label>
            <div class="select-wrapper">
              <select name="plan_id" id="renew_plan_id" class="styled-select">
                <option value="">-- اترك نفس الخطة --</option>
                @foreach(\App\Models\SubscriptionPlan::orderBy('price')->get() as $p)
                  <option value="{{ $p->id }}" {{ $p->id == $plan->id ? 'selected' : '' }}>
                    {{ $p->name }} — {{ number_format($p->price,2) }} جنيه — {{ $p->visits_count }} زيارات — {{ $p->duration_days }} يوم
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="actions-row">
            <button type="button" class="btn-small" id="renewCancel">إلغاء</button>
            <button type="submit" class="btn-gold" id="renewConfirm">✅ تجديد الآن</button>
          </div>
        </form>

        <div id="renewModalMessage" class="modal-message"></div>
      </div>
    </div>
  </div>
</div>

<style>
  :root {
    --gold-dark: linear-gradient(135deg, #b8860b, #d4af37);
    --gold-hover: linear-gradient(135deg, #e6c200, #ffde5a);
  }

  .custom-modal {
    position: fixed;
    inset: 0;
    z-index: 99990;
    display: flex;
    align-items: center;
    justify-content: center;
    direction: rtl;
  }

  .custom-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
  }

  .custom-modal-dialog {
    position: relative;
    max-width: 560px;
    width: 100%;
    z-index: 99991;
    animation: fadeInUp 0.3s ease;
  }

  .custom-modal-content {
    background: #2b2b2b; /* كارت دارك */
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
    color: #fff;
  }

  .custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .custom-modal-title {
    color: #ffd700;
    font-size: 20px;
    margin: 0;
  }

  .muted {
    color: #ccc;
    margin-bottom: 14px;
  }

  .form-label {
    color: #fff;
    font-weight: 600;
  }

  .styled-select {
    width: 100%;
    background: #1e1e1e;
    border: 1px solid #444;
    color: #fff;
    border-radius: 12px;
    padding: 12px 40px 12px 12px;
    appearance: none;
    font-size: 15px;
    cursor: pointer;
    text-align: right;
  }

  .styled-select:focus {
    border-color: #666;
    outline: none;
  }

  .select-wrapper {
    position: relative;
  }

  .select-wrapper::after {
    content: "";
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-size: contain;
  }

  .btn-small {
    background: #3a3a3a;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
  }

  .btn-small:hover {
    background: #555;
  }

  .btn-gold {
    background: var(--gold-dark);
    color: #1b1b1b;
    border: none;
    border-radius: 12px;
    padding: 10px 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-gold:hover {
    background: var(--gold-hover);
    transform: scale(1.05);
  }

  .actions-row {
    margin-top: 14px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  .modal-message {
    margin-top: 10px;
    color: #ffd700;
    font-weight: 600;
    min-height: 20px;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
