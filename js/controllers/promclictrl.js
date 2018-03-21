(function(){

    var promclictrl = angular.module('cpm.promclictrl', []);

    promclictrl.controller('promCliCtrl', ['$scope', '$confirm', 'DTOptionsBuilder', 'paisSrvc', 'tituloSrvc', 'promotorSrvc', 'clienteSrvc', '$filter', function($scope, $confirm, DTOptionsBuilder, paisSrvc, tituloSrvc, promotorSrvc, clienteSrvc, $filter){
        $scope.lstPaises = [];
        $scope.lstTitulos = [];
        $scope.lstPromotores = [];//1
        $scope.lstClientes = [];//2

        $scope.objeto = {};

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);

        tituloSrvc.lstTitulos().then(function(d){ $scope.lstTitulos = d; });
        paisSrvc.lstPaises().then(function(d){ $scope.lstPaises = d; });

        $scope.resetObjeto = function(){ $scope.objeto = {}; goTop(); };

        $scope.loadData = function(cualData){
            switch(cualData){
                case 1:
                    promotorSrvc.lstPromotores().then(function(d){ $scope.lstPromotores = d; }); break;
                case 2:
                    clienteSrvc.lstAllClientes().then(function(d){
                        for(var i = 0; i < d.length; i++){
                            d[i].id = parseInt(d[i].id);
                            d[i].idpromotor = parseInt(d[i].idpromotor);
                            d[i].idtitulo = parseInt(d[i].idtitulo);
                            d[i].idpais = parseInt(d[i].idpais);
                        }
                        $scope.lstClientes = d;
                    }); break;
            }
        };

        for(var i = 1; i <= 2; i++){ $scope.loadData(i); }

        $scope.loadOne = function(cualData, iddata){
            switch(cualData){
                case 1:
                    promotorSrvc.getPromotor(iddata).then(function(d){ $scope.objeto = d[0]; }); break;
                case 2:
                    clienteSrvc.getCliente(iddata).then(function(d){
                        for(var i = 0; i < d.length; i++){
                            d[i].id = parseInt(d[i].id);
                            d[i].idpromotor = parseInt(d[i].idpromotor);
                            d[i].idtitulo = parseInt(d[i].idtitulo);
                            d[i].idpais = parseInt(d[i].idpais);
                        }
                        $scope.objeto = d[0];
                        $scope.objeto.objPromotor = $filter('getById')($scope.lstPromotores, $scope.objeto.idpromotor);
                        $scope.objeto.objTitulo = $filter('getById')($scope.lstTitulos, $scope.objeto.idtitulo);
                        $scope.objeto.objPais = $filter('getById')($scope.lstPaises, $scope.objeto.idpais);
                        goTop();
                    }); break;
            }
        };

        $scope.ejecutar = function(qCosa, obj, aQuien){
            switch(aQuien){
                case 1:
                    promotorSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; }); break;
                case 2:
                    obj.idpromotor = obj.objPromotor.id;
                    obj.idtitulo = obj.objTitulo.id;
                    obj.telefono = obj.telefono != null && obj.telefono != undefined ? obj.telefono : '';
                    obj.puesto = obj.puesto != null && obj.puesto != undefined ? obj.puesto : '';
                    obj.empresa = obj.empresa != null && obj.empresa != undefined ? obj.empresa : '';
                    obj.direccion = obj.direccion != null && obj.direccion != undefined ? obj.direccion : '';
                    obj.idpais = obj.objPais != null && obj.objPais != undefined ? obj.objPais.id : 0;
                    obj.nit = obj.nit != null && obj.nit != undefined ? obj.nit : '';
                    clienteSrvc.editRow(obj, qCosa).then(function(){ $scope.loadData(aQuien); $scope.objeto = {}; });
                    break;
            }
        };

        $scope.updCliente = function(obj){
            obj.idpromotor = obj.objPromotor.id;
            obj.idtitulo = obj.objTitulo.id;
            obj.telefono = obj.telefono != null && obj.telefono != undefined ? obj.telefono : '';
            obj.puesto = obj.puesto != null && obj.puesto != undefined ? obj.puesto : '';
            obj.empresa = obj.empresa != null && obj.empresa != undefined ? obj.empresa : '';
            obj.direccion = obj.direccion != null && obj.direccion != undefined ? obj.direccion : '';
            obj.idpais = obj.objPais != null && obj.objPais != undefined ? obj.objPais.id : 0;
            obj.nit = obj.nit != null && obj.nit != undefined ? obj.nit : '';
            clienteSrvc.editRow(obj, 'u').then(function(){
                $scope.loadData(2);
                $scope.resetObjeto();
            });
        };

        $scope.eliminarRegistro = function(qCosa, obj, aQuien){
            $confirm({text: '¿Seguro(a) de eliminar este registro?', title: 'Eliminar registro', ok: 'Sí', cancel: 'No'}).then(function() {
                $scope.ejecutar(qCosa, obj, aQuien);
            });
        };

    }]);

}());
