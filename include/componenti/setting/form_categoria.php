
<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $madri=$database->select('cat_merc','id,categoria', 'sub=0');
    if(isset($_POST['id']) && $_POST['id']!=''){
        $azione='update';
        $id=$_POST['id'];
        $cat=$database->select('cat_merc','*',"id=$id");
        $categoria=$cat[0]['categoria'];
        $madre=$cat[0]['sub'];
    } else {
        $azione='new';
        $categoria='';
        $madre='';
    }
    $database->disconnect();
?>
<form id="categ" class="form-validate is-alter" novalidate="novalidate">
<?php if ($azione=='update'):?>
    <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
<?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="categoria">Categoria</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="categoria" required="" value="<?php echo $categoria; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="madre">Categoria Madre</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" data-placeholder="Categoria Madre" id="sub" style="line-height:14px">
            <option value="0" <?php if($madre==0) echo 'selected'; ?>>Categoria Madre</option>    
            <?php foreach($madri as $cate):?>
                <option value="<?php echo $cate['id']; ?>" <?php if($cate['id']==$madre) echo 'selected'; ?>><?php echo $cate['categoria']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    
    <div class="form-group">
        <button type="button" onClick="raccoltadati('<?php echo $azione; ?>','categ')" class="btn btn-lg btn-primary">Salva informazioni</button>
    </div>
</form>

