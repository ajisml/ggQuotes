<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate Quotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .top-90{top:78%!important}.start-95{left:95%!important}.btn-check:checked+.btn,.btn.active,.btn.show,.btn:first-child:active,:not(.btn-check)+.btn:active{background-color:#212529;border-radius:0;border:0;color:#fff;border-bottom:1px solid #333}.thumbnail{width: 100%;height: 130pt;object-fit: cover;object-position: center; }
    </style>
  </head>
  <body class="bg-body-secondary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mt-5 border-black">
                    <div class="card-body shadow">
                        <div class="mb-3">
                            <label for="searchImg" class="form-label fw-bold">Background Quotes</label>
                            <input type="text" placeholder="Contoh : Ikan Aquarium.." id="searchImg" name="search_img" class="form-control rounded-0 border-black">
                        </div>
                        <div class="mb-3">
                            <input type="radio" class="btn-check" name="type_quotes" id="typeQuotes5" autocomplete="off" value="sad">
                            <label class="btn" for="typeQuotes5">Sedih</label>

                            <input type="radio" class="btn-check" name="type_quotes" id="typeQuotes6" autocomplete="off" value="spirit">
                            <label class="btn" for="typeQuotes6">Semangat</label>

                            <input type="radio" class="btn-check" name="type_quotes" id="typeQuotes7" autocomplete="off" value="wise">
                            <label class="btn" for="typeQuotes7">Bijak</label>

                            <input type="radio" class="btn-check" name="type_quotes" id="typeQuotes8" autocomplete="off" value="funny">
                            <label class="btn" for="typeQuotes8">Lucu</label>
                        </div>
                        <div class="mb-3">
                            <label for="fonts" class="form-label fw-bold">Pilih Fonts</label>
                            <select name="fonts" id="fonts" class="form-control rounded-0 border-black">
                                <option value="">-- Pilih Fonts --</option>
                                <?php foreach($fonts as $font){ ?>
                                    <option value="<?= $font ?>"><?= preg_replace('/\.(ttf|otf|woff)$/', '', $font) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-dark rounded-0 btn-generate"><i class="fas fa-paper-plane"></i> Generate</button>
                        </div>
                    </div>
                    <i class="fas fa-cog fa-4x position-absolute top-90 start-95 spinner-process"></i>
                </div>
                <div class="row" id="result">
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" integrity="sha512-GWzVrcGlo0TxTRvz9ttioyYJ+Wwk9Ck0G81D+eO63BaqHaJ3YZX9wuqjwgfcV/MrB2PhaVX9DkYVhbFpStnqpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function(){
            function list(){
                $.ajax({
                    url : "<?= base_url('api/listQuotes') ?>",
                    type : "GET",
                    dataType : "JSON",
                    success : (res)=>{
                        if(res.status === true){
                            var html = "";
                            var i;
                            var row = res.data;
                            for(i = 0; i < row.length; i++){
                                html +=
                                `
                                <div class="col-lg-4 mt-5">
                                    <div class="area-img">
                                        <img src='<?= base_url('assets/images') ?>/${row[i]}' class='thumbnail'>
                                    </div>
                                </div>
                                `;
                            }
                            $("#result").html(html);
                        }else{
                            $("#result").html(`<p class='text-center mt-5'>${res.data}</p>`);
                        }
                    }, error : (resErr)=>{

                    }
                })
            }
            list();
            $(".btn-generate").click(()=>{
                var search_img = $("#searchImg").val();
                var type_quotes = $("input[name=type_quotes]:checked").val();
                var fonts       =   $("#fonts").val();
                $.ajax({
                    url : "<?= base_url('api/pGenerate') ?>",
                    type : "POST",
                    dataType : "JSON",
                    data : { search_img, type_quotes, fonts },
                    beforeSend : ()=>{
                        $(".btn-generate").html(`<i class='fas fa-spinner fa-spin'></i> Wait..`).prop('disabled', true);
                        $(".spinner-process").addClass('fa-spin');
                    }, success : (res)=>{
                        list();
                        $(".btn-generate").html(`<i class="fas fa-paper-plane"></i> Generate`).prop('disabled', false);
                        $(".spinner-process").removeClass('fa-spin');
                        if(res.status === true){
                            Swal.fire({
                                title : "Yeay! Berhasil",
                                html : res.data,
                                icon : "success"
                            });
                        }else{
                            Swal.fire({
                                title : "Terjadi kesalahan, coba lagi",
                                html : res.data,
                                icon : "error"
                            });
                        }
                    }, error : (resErr)=>{
                        $(".spinner-process").removeClass('fa-spin');
                        $(".btn-generate").html(`<i class="fas fa-paper-plane"></i> Generate`).prop('disabled', false);
                        Swal.fire({
                            title : "Error, Coba lagi",
                            html : resErr.status +" "+ resErr.statusText,
                            icon : "error"
                        });
                    }
                });
            })
        });
    </script>
  </body>
</html>