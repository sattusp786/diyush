{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right"><a href="{{ add }}" data-toggle="tooltip" title="{{ button_add }}" class="btn btn-primary"><i class="fa fa-plus"></i></a>
        <button type="button" data-toggle="tooltip" title="{{ button_delete }}" class="btn btn-danger" onclick="confirm('{{ text_confirm }}') ? $('#form-marketing').submit() : false;"><i class="fa fa-trash-o"></i></button>
      </div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="{{ breadcrumb['href'] }}">{{ breadcrumb['text'] }}</a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ success }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> {{ text_list }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ delete }}" method="post" enctype="multipart/form-data" id="form-marketing">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-left"><?php if ($sort == 'short_description') { ?>
                    <a href="{{ sort_short_description }}" class="{{ strtolower($order) }}">{{ column_name }}</a>
                    <?php } else { ?>
                    <a href="{{ sort_short_description }}">{{ column_name }}</a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'Subject') { ?>
                    <a href="{{ sort_subject }}" class="{{ strtolower($order) }}">{{ column_subject }}</a>
                    <?php } else { ?>
                    <a href="{{ sort_subject }}">{{ column_subject }}</a>
                    <?php } ?></td>
				  <td class="text-left"><?php if ($sort == 'code') { ?>
                    <a href="{{ sort_code }}" class="{{ strtolower($order) }}">{{ column_code }}</a>
                    <?php } else { ?>
                    <a href="{{ sort_code }}">{{ column_code }}</a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'sort_order') { ?>
                    <a href="{{ sort_sort_order }}" class="{{ strtolower($order) }}">{{ column_sort_order }}</a>
                    <?php } else { ?>
                    <a href="{{ sort_sort_order }}">{{ column_sort_order }}</a>
                    <?php } ?></td>
                  <td class="text-right">{{ column_action }}</td>
                </tr>
              </thead>
              <tbody>
                <?php if ($email_managers) { ?>
                <?php foreach ($email_managers as $email_manager) { ?>
                <tr>
                  <td class="text-center"><?php if ($email_manager['selected']) {  ?>
                    <input type="checkbox" name="selected[]" value="{{ email_manager['email_manager_id'] }}" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="{{ email_manager['email_manager_id'] }}" />
                    <?php } ?></td>
                  <td class="text-left">{{ email_manager['short_description'] }}</td>
                  <td class="text-left">{{ email_manager['subject'] }}</td>
                  <td class="text-right">{{ email_manager['code'] }}</td>
                  <td class="text-right">{{ email_manager['sort_order'] }}</td>
                  <td class="text-right">
					<?php foreach ($email_manager['action'] as $action) { ?>
					<a href="{{ action['href'] }}" data-toggle="tooltip" title="{{ action['text'] }}" class="btn btn-primary"><i class="fa fa-pencil"></i> </a>
					<?php } ?>
				  </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="8">{{ text_no_results }}</td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        <div class="row">
          <div class="col-sm-6 text-left">{{ pagination }}</div>
          <div class="col-sm-6 text-right">{{ results }}</div>
        </div>
      </div>
    </div>
  </div>
  </div>
{{ footer }}