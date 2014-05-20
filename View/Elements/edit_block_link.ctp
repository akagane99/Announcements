<?php /* 各プラグインではなく親で行うべき */ ?>
<div class="text-right" ng-controller="blockEditController">
<?php
	echo $this->Html->link(__d('announcements', 'Block edit'), array('action' => 'block_setting', $block_id), array('class' => 'btn btn-primary', 'ng-click' => 'show($event)'));
?>
</div>
<?php $this->Html->script('/net_commons/angular/angular.min.js', array('inline' => false));?>
<?php $this->Html->script('/net_commons/angular-bootstrap/ui-bootstrap-tpls.min.js', array('inline' => false));?>
<?php $this->Html->script('/announcements/js/announcements.js', array('inline' => false));?>
<?php $this->Html->css('/announcements/css/announcements.css', array('inline' => false));?>
