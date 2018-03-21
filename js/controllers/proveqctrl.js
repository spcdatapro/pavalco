(function(){

    var proveqctrl = angular.module('cpm.proveqctrl', []);

    proveqctrl.controller('provEqCtrl', ['$scope', '$confirm', 'DTOptionsBuilder', 'tituloSrvc', 'provEquipoSrvc', 'sectorSrvc', '$filter', function($scope, $confirm, DTOptionsBuilder, tituloSrvc, provEquipoSrvc, sectorSrvc, $filter){
        $scope.lstTitulos = [];
        $scope.lstProvsEq = [];
        $scope.sector = {descsector: ''};
        $scope.sectores = [];

        $scope.objeto = {};

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);

        tituloSrvc.lstTitulos().then(function(d){ $scope.lstTitulos = d; });

        $scope.resetObjeto = function(){ $scope.objeto = {}; };

        $scope.loadData = function(){
            provEquipoSrvc.lstProvsEq().then(function(d){
                for(var i = 0; i < d.length; i++){ d[i].idtitulosug = parseInt(d[i].idtitulosug); };
                $scope.lstProvsEq = d;
            });
        };

        //for(var i = 1; i <= 2; i++){ $scope.loadData(); };
        $scope.loadData();

        $scope.ejecutar = function(qCosa, obj){
            obj.idtitulosug = obj.objTitulo != null && obj.objTitulo != undefined ? obj.objTitulo.id : 0;
            obj.contactosug = obj.contactosug != null && obj.contactosug != undefined ? obj.contactosug : '';
            obj.telsug = obj.telsug != null && obj.telsug != undefined ? obj.telsug : '';
            obj.correosug = obj.correosug != null && obj.correosug != undefined ? obj.correosug : '';
            provEquipoSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(); $scope.objeto = {}; });
        };

        $scope.eliminarRegistro = function(qCosa, obj){
            $confirm({text: '¿Seguro(a) de eliminar este proveedor de equipos?', title: 'Eliminar proveedor de equipos', ok: 'Sí', cancel: 'No'}).then(function() {
                $scope.ejecutar(qCosa, obj);
            });
        };

        $scope.getProvEquipo = function(idprov){
            provEquipoSrvc.getProvEq(+idprov).then(function(d){
                d[0].idtitulosug = parseInt(d[0].idtitulosug);                
                $scope.objeto = d[0];
                $scope.objeto.objTitulo = $filter('getById')($scope.lstTitulos, $scope.objeto.idtitulosug);
            });
        };

        $scope.getLstSectores = function(){
            sectorSrvc.lstSectores().then(function(d){
                for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); }
                $scope.sectores = d;
            });
        };

        $scope.addSector = function(obj){
            sectorSrvc.editRow(obj, 'c').then(function(){
                $scope.getLstSectores();
                $scope.sector = {descsector: ''};
            });
        };

        $scope.updSector = function(data, id){
            data.id = id;
            sectorSrvc.editRow(data, 'u').then(function(){
                $scope.getLstSectores();
            });
        };

        $scope.delSector = function(id){
            $confirm({text: '¿Seguro(a) de eliminar este sector?', title: 'Eliminar sector', ok: 'Sí', cancel: 'No'}).then(function() {
                sectorSrvc.editRow({id: id}, 'd').then(function(){ $scope.getLstSectores(); });
            });
        };

        $scope.getLstSectores();

    }]);

}());
