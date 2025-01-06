<div class="card pc-user-card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url() ?>public/assets/images/user/avatar-1.jpg" alt="user-image" class="user-avtar wid-45 rounded-circle" />
            </div>
            <div class="flex-grow-1 ms-3 me-2">
                <h6 class="mb-0"><?= session()->nombre ." ". session()->apellidos ?></h6>
                <small><?= session()->perfil ?></small>
            </div>
        </div>
    </div>
</div>