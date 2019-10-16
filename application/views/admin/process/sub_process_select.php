<div class="form-group mbot25 items-wrapper select-placeholder<?php if(has_permission('items', '', 'create')){
    echo ' input-group-select';
} ?>">
    <div class="<?php if(has_permission('items', '', 'create')){
        echo 'input-group input-group-select';
    } ?>">
        <div class="items-select-wrapper">
            <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){
                echo ' ajax-search';
            } ?><?php if(has_permission('items', '', 'create')){
                echo ' _select_input_group';
            } ?>" data-width="100%" id="sub_process_item_select" data-none-selected-text="<?php echo _l('add_sub_process'); ?>"
                    data-live-search="true">
                <option value=""></option>
                <?php foreach($sub_process_list as $sub_process){ ?>
                    <option value="<?php echo $sub_process['id']; ?>"
                            data-subtext="<?php echo $sub_process['name']; ?>"><?php echo $sub_process['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="input-group-addon">
            <a href="#" data-toggle="modal" data-target="#sales_item_modal">
                <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>

</div>
