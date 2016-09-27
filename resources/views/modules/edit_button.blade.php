<div class="row">
    <div class="col-sm-12 center">
        <button class="btn btn-primary" style='width: 150px' ng-click="FormService.edit()" ng-disabled="FormService.saving">
            @{{ FormService.saving ? 'редактировать' : 'редактировать' }}
        </button>
    </div>
</div>
