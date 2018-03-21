(function(){

    var rptconciliabcoctrl = angular.module('cpm.rptconciliabcoctrl', []);

    rptconciliabcoctrl.controller('rptConciliaBcoCtrl', ['$scope', 'rptConciliaBcoSrvc', 'empresaSrvc', 'authSrvc', 'jsReportSrvc', '$sce', 'bancoSrvc', '$uibModal', function($scope, rptConciliaBcoSrvc, empresaSrvc, authSrvc, jsReportSrvc, $sce, bancoSrvc, $uibModal){

        $scope.params = {idbanco: undefined, del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), saldobco: 0.00, objBanco: {}, resumido: 1, idusuario: 0};
        $scope.bancos = [];
        $scope.conciliacion = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.params.idempresa = parseInt(usrLogged.workingon);
                $scope.params.idusuario = parseInt(usrLogged.uid);
                bancoSrvc.lstBancos($scope.params.idempresa).then(function(d){ $scope.bancos = d; });
            }
        });

        $scope.getConciliacion = function(){
            //$scope.params.idbanco = $scope.params.objBanco.id;
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.saldobco = $scope.params.saldobco != null && $scope.params.saldobco != undefined ? $scope.params.saldobco : 0.00;
            $scope.params.resumido = $scope.params.resumido != null && $scope.params.resumido != undefined ? $scope.params.resumido : 0;
            //console.log($scope.params); return;
            jsReportSrvc.conciliacionBancaria($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                $scope.content = $sce.trustAsResourceUrl(fileURL);
            });
        };

        $scope.getConciliacionXlsx = function(){
            //$scope.params.idbanco = $scope.params.objBanco.id;
            $scope.params.fdelstr = moment($scope.params.del).format('YYYY-MM-DD');
            $scope.params.falstr = moment($scope.params.al).format('YYYY-MM-DD');
            $scope.params.saldobco = $scope.params.saldobco != null && $scope.params.saldobco != undefined ? $scope.params.saldobco : 0.00;
            $scope.params.resumido = $scope.params.resumido != null && $scope.params.resumido != undefined ? $scope.params.resumido : 0;
            jsReportSrvc.conciliacionBancariaXlsx($scope.params).then(function(result){
                var file = new Blob([result.data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                saveAs(file, 'ConciliacionBancaria.xlsx');
            });
        };

        $scope.openHisto = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalHistoCon.html',
                controller: 'ModalHistoConCtrl',
                windowClass: 'app-modal-window',
                resolve:{ idempresa: function(){ return $scope.params.idempresa; } }
            });

            modalInstance.result.then(function(selected){
                //console.log(selected);
                $scope.params = selected;
            }, function(){ return 0; });
        };

        $scope.saveHisto = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalSaveHistoCon.html',
                controller: 'ModalSaveHistoConCtrl',
                resolve:{ obj: function(){ return $scope.params; } }
            });

            modalInstance.result.then(function(){
            }, function(){ return 0; });
        }
    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//
    rptconciliabcoctrl.controller('ModalHistoConCtrl', ['$scope', '$uibModalInstance', 'idempresa', 'rptConciliaBcoSrvc', function($scope, $uibModalInstance, idempresa, rptConciliaBcoSrvc){

        $scope.histo = [];
        $scope.selected = {};

        rptConciliaBcoSrvc.historial(idempresa).then(function(d){
            $scope.histo = d;
        });

        $scope.ok = function (obj) {
            rptConciliaBcoSrvc.getcon(+obj.id).then(function(d){
                $scope.selected = d[0];
                $scope.selected.del = moment($scope.selected.fdel).toDate();
                $scope.selected.al = moment($scope.selected.fal).toDate();
                $scope.selected.saldobco = parseFloat(parseFloat($scope.selected.saldobco).toFixed(2));
                $scope.selected.resumido = parseInt($scope.selected.resumido);
                $scope.selected.idusuario = parseInt($scope.selected.guardadopor);
                $uibModalInstance.close($scope.selected);
            });
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);
    //------------------------------------------------------------------------------------------------------------------------------------------------//
    rptconciliabcoctrl.controller('ModalSaveHistoConCtrl', ['$scope', '$uibModalInstance', 'obj', 'rptConciliaBcoSrvc', function($scope, $uibModalInstance, obj, rptConciliaBcoSrvc){

        $scope.histo = obj;
        $scope.histo.descripcion = "";

        $scope.ok = function () {
            $scope.histo.fdelstr = moment($scope.histo.del).format('YYYY-MM-DD');
            $scope.histo.falstr = moment($scope.histo.al).format('YYYY-MM-DD');
            rptConciliaBcoSrvc.editRow($scope.histo, 'c').then(function(){
                $uibModalInstance.close();
            });
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

}());

