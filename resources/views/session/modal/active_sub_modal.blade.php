<div class="modal fade" id="activeSubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">

            <div class="modal-header">
                <h5 class="modal-title">تنبيه اشتراك نشط</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center" style="font-size: 17px;">
                هذا العميل لديه اشتراك نشط.<br>
                ماذا تريد أن تفعل؟
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <!-- زر بدء جلسة -->
                <form action="{{ route('session.store.manager') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ session('return_phone') }}">
                    <input type="hidden" name="name" value="{{ session('return_name') }}">
                    <input type="hidden" name="persons" value="{{ session('return_persons') }}">
                    <input type="hidden" name="session_for_subscriber" value="1">
                    <button class="btn btn-success w-100">بدء جلسة</button>
                </form>

            @if(session('active_subscription_id'))
    <form action="{{ route('subscriptions.decrease_from_sessions', ['subscription' => session('active_subscription_id')]) }}" method="POST">
        @csrf
        <button class="btn btn-warning w-100" style="margin-left:10px;">خصم زيارة</button>
    </form>
@endif

            </div>
        </div>
    </div>
</div>
