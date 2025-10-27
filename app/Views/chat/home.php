<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">
    <div class="row"><!-- [ sample-page ] start -->
        <div class="col-md-12">
            <div class="card construction-card">
                <div class="card-body">
                    <div class="construction-image-block">
                        <div class="row justify-content-center">
                            <div class="col-10">
                                <img class="img-fluid" src="assets/chat/img/contable3.png" width="550" height="550" alt="img">
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="<?= base_url('chat-whatsapp') ?>" target="__blank" class="btn btn-primary mb-3">Ir al Chat</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>