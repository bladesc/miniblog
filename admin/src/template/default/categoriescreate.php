<?php include 'pageup.php' ?>

    <form action="index.php?pageadmin=category&action=create" method="post">
        <div>
            Wprowadz nazwe kategorii
            <input type="text" name="cName" value="<?= $this->request->post()->get('cName') ?>">
        </div>
        <div>
            <select name="cStatus">
                <option value="1" <?php if ($this->request->post()->get('cStatus') == 1): ?> selected <?php endif; ?>>
                    Aktywny
                </option>
                <option value="2" <?php if ($this->request->post()->get('cStatus') == 2): ?> selected <?php endif; ?>>
                    Nieaktywny
                </option>
            </select>
        </div>
        <div>
            <input type="submit" name="cSubmin" value="Dodaj">
        </div>
    </form>
<?php
print_r($this->data);
?>
<?php include 'pagedown.php' ?>