(function(){

    var mntgenclientesctrl = angular.module('cpm.mntgenclientesctrl', []);

    mntgenclientesctrl.controller('mntGenClientesCtrl', ['$scope', '$confirm', 'DTOptionsBuilder', 'paisSrvc', 'tituloSrvc', 'periodicidadSrvc', 'cobroPrimRentaSrvc', 'tipoFinancieraSrvc', 'financieraSrvc', function($scope, $confirm, DTOptionsBuilder, paisSrvc, tituloSrvc, periodicidadSrvc, cobroPrimRentaSrvc, tipoFinancieraSrvc, financieraSrvc){
        $scope.lstPaises = []; //1
        $scope.lstTitulos = [];//2
        $scope.lstPeriodicidad = [];//3
        $scope.lstCobroPrimRenta = [];//4
        $scope.lstTiposFinanciera = [];//5
        $scope.lstFinancieras = [];//6

        $scope.objeto = {};

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);

        $scope.loadData = function(cualData){
            switch(cualData){
                case 1:
                    paisSrvc.lstPaises().then(function(d){ $scope.lstPaises = d; }); break;
                case 2:
                    tituloSrvc.lstTitulos().then(function(d){ $scope.lstTitulos = d; }); break;
                case 3:
                    periodicidadSrvc.lstPeriodicidad().then(function(d){ $scope.lstPeriodicidad = d; }); break;
                case 4:
                    cobroPrimRentaSrvc.lstCobroPrimRenta().then(function(d){ $scope.lstCobroPrimRenta = d; }); break;
                case 5:
                    tipoFinancieraSrvc.lstTiposFinanciera().then(function(d){ $scope.lstTiposFinanciera = d; }); break;
                case 6:
                    financieraSrvc.lstAllFinancieras().then(function(d){
                        for(var x = 0; x < d.length; x++){ d[x].idtipofinanciera = parseInt(d[x].idtipofinanciera); };
                        $scope.lstFinancieras = d;
                    });
                    break;
            };
        };

        for(var i = 1; i <= 6; i++){ $scope.loadData(i); };

        $scope.ejecutar = function(qCosa, obj, aQuien){
            switch(aQuien){
                case 1:
                    paisSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 2:
                    tituloSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 3:
                    periodicidadSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 4:
                    cobroPrimRentaSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 5:
                    tipoFinancieraSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 6:
                    if(qCosa !== 'd'){ obj.idtipofinanciera = parseInt(obj.objTipoFinanciera.id); };
                    financieraSrvc.editRow(obj, qCosa).then(function(){
                        $scope.loadData(aQuien); $scope.objeto = {};
                    });
                    break;
            };
        };

        $scope.eliminarRegistro = function(qCosa, obj, aQuien){
            $confirm({text: '¿Seguro(a) de eliminar este registro?', title: 'Eliminar registro', ok: 'Sí', cancel: 'No'}).then(function() {
                $scope.ejecutar(qCosa, obj, aQuien);
            });
        };

    }]);

}());
