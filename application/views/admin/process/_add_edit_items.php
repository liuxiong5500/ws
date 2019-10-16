<div class="panel-body mtop10">
    <div class="row col-md-4">
        <?php $this->load->view('admin/process/sub_process_select'); ?>
    </div>
    <div class="row col-md-12 table-responsive s_table">
        <table class="table items no-mtop">
            <thead>
            <tr>
                <th width="50%" align="left"><?php echo _l('process_name'); ?></th>
                <th width="45%" align="left"><?php echo _l('process_remarks'); ?></th>
                <th align="center"><i class="fa fa-edit"></i></th>
            </tr>
            </thead>
            <tbody id="process_item_list_table">
            <?php foreach($processItem as $item): ?>
                <tr class="process_item_line item">
                    <td><input title="" type="hidden" name="process_item[id][]" disabled class="form-control"
                               value="<?php echo $item->id; ?>"/>
                        <input title="" type="text" name="process_item[name][]" disabled class="form-control"
                               value="<?php echo $item->name; ?>"/></td>
                    <td><input title="" type="text" name="process_item[remarks][]" disabled class="form-control"
                               value="<?php echo $item->remarks; ?>"></td>
                    <td><a class="btn btn-danger btn-delete">删除</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

