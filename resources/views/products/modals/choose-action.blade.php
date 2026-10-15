<div class="modal fade animate__animated animate__zoomIn" id="chooseActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header text-dark rounded-top-4 d-flex align-items-center"
                style="background-color: #d9b3ff;">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto">⚡ اختر العملية</h5>
            </div>
            <div class="modal-body text-center py-4">

                <button class="btn btn-lg w-100 mb-3 fw-bold btn-purple" data-bs-target="#addProductModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                    ➕ إضافة منتج جديد
                </button>

                <button class="btn btn-lg w-100 mb-3 fw-bold btn-blue" data-bs-target="#addCategoryModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                    📂 إضافة صنف جديد (قسم)
                </button>

                <button class="btn btn-lg w-100 fw-bold btn-green" data-bs-target="#addQuantityModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                    📦 إضافة كمية لمنتج موجود
                </button>

            </div>
        </div>
    </div>
</div>

<style>
    /* أضف هذا الاستايل للزر الجديد */
    .btn-blue {
        background-color: #3498db;
        color: white;
    }

    .btn-blue:hover {
        background-color: #2980b9;
        color: #fff;
    }
</style>
