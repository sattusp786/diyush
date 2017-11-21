{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-category" data-toggle="tooltip" title="{{ button_save }}" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="{{ cancel }}" data-toggle="tooltip" title="{{ button_cancel }}" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_form }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-category" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab">{{ tab_general }}</a></li>
            <li><a href="#tab-data" data-toggle="tab">{{ tab_data }}</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <ul class="nav nav-tabs" id="language">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#language{{ language['language_id'] }}" data-toggle="tab"><img src="language/{{ language['code'] }}/{{ language['code'] }}.png" title="{{ language['name'] }}" /> {{ language['name'] }}</a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="language{{ language['language_id'] }}">
                  <div class="form-group required">
                    <label class="col-sm-2 control-label" for="input-name{{ language['language_id'] }}">{{ entry_subject }}</label>
                    <div class="col-sm-10">
                      <input type="text" name="email_manager_description[{{ language['language_id'] }}][subject]" value="<?php echo isset($email_manager_description[$language['language_id']]) ? $email_manager_description[$language['language_id']]['subject'] : '' }}" placeholder="{{ entry_subject }}" id="input-name{{ language['language_id'] }}" class="form-control" />
					  <?php if (isset($error_short_description[$language['language_id']])) { ?>
						<span class="error">{{ error_short_description[$language['language_id']] }}</span>
						<?php } ?>
                      <?php if (isset($error_name[$language['language_id']])) { ?>
                      <div class="text-danger">{{ error_name[$language['language_id']] }}</div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-description{{ language['language_id'] }}">{{ entry_short_description }}</label>
                    <div class="col-sm-10">
                      <textarea name="email_manager_description[{{ language['language_id'] }}][short_description]" placeholder="{{ entry_short_description }}" id="input-description{{ language['language_id'] }}" class="form-control"><?php echo isset($email_manager_description[$language['language_id']]) ? $email_manager_description[$language['language_id']]['short_description'] : '' }}</textarea>
					  <?php if (isset($error_subject[$language['language_id']])) { ?>
						<span class="error">{{ error_subject[$language['language_id']] }}</span>
						<?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-description{{ language['language_id'] }}">{{ entry_content }}</label>
                    <div class="col-sm-10">
                      <textarea name="email_manager_description[{{ language['language_id'] }}][content]" placeholder="{{ entry_content }}" id="input-description{{ language['language_id'] }}" class="form-control summernote"><?php echo isset($email_manager_description[$language['language_id']]) ? $email_manager_description[$language['language_id']]['content'] : '' }}</textarea>
					  <?php if (isset($error_description[$language['language_id']])) { ?>
						<span class="error">{{ error_description[$language['language_id']] }}</span>
						<?php } ?>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
            </div>
            <div class="tab-pane" id="tab-data">
              <div class="form-group">
                <label class="col-sm-2 control-label">{{ entry_store }}</label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 150px; overflow: auto;">
                    <div class="checkbox">
                      <label>
                        <?php if (in_array(0, $email_manager_store)) { ?>
                        <input type="checkbox" name="email_manager_store[]" value="0" checked="checked" />
                        {{ text_default }}
                        <?php } else { ?>
                        <input type="checkbox" name="email_manager_store[]" value="0" />
                        {{ text_default }}
                        <?php } ?>
                      </label>
                    </div>
                    <?php foreach ($stores as $store) { ?>
                    <div class="checkbox">
                      <label>
                        <?php if (in_array($store['store_id'], $email_manager_store)) { ?>
                        <input type="checkbox" name="email_manager_store[]" value="{{ store['store_id'] }}" checked="checked" />
                        {{ store['name'] }}
                        <?php } else { ?>
                        <input type="checkbox" name="email_manager_store[]" value="{{ store['store_id'] }}" />
                        {{ store['name'] }}
                        <?php } ?>
                      </label>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-code"><span data-toggle="tooltip" title="{{ help_code }}">{{ entry_code }}</span></label>
                <div class="col-sm-10">
                  <input type="text" name="code" value="{{ code }}" placeholder="{{ entry_code }}" id="input-code" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sort-order">{{ entry_sort_order }}</label>
                <div class="col-sm-10">
                  <input type="text" name="sort_order" value="{{ sort_order }}" placeholder="{{ entry_sort_order }}" id="input-sort-order" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status">{{ entry_status }}</label>
                <div class="col-sm-10">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status) { ?>
                    <option value="1" selected="selected">{{ text_enabled }}</option>
                    <option value="0">{{ text_disabled }}</option>
                    <?php } else { ?>
                    <option value="1">{{ text_enabled }}</option>
                    <option value="0" selected="selected">{{ text_disabled }}</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="view/javascript/summernote/summernote.js"></script>
  <link href="view/javascript/summernote/summernote.css" rel="stylesheet" />
  <script type="text/javascript" src="view/javascript/summernote/opencart.js"></script> 
 <script type="text/javascript"><!--
$('#language a:first').tab('show');
//--></script>
 </div>
{{ footer }}