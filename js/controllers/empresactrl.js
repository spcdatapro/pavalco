(function(){

    var empresactrl = angular.module('cpm.empresactrl', ['cpm.empresasrvc']);

    empresactrl.controller('empresaCtrl', ['$scope', 'empresaSrvc', 'monedaSrvc', 'tipoConfigContaSrvc', 'cuentacSrvc', '$confirm', 'authSrvc', '$route', function($scope, empresaSrvc, monedaSrvc, tipoConfigContaSrvc, cuentacSrvc, $confirm, authSrvc, $route){

        $scope.laEmpresa = {};
        $scope.lstEmpresas = [];
        $scope.lasMonedas = [];
        $scope.editando = false;
        $scope.etiqueta = "";
        $scope.laConfConta = {};
        $scope.lasConfsConta = [];
        $scope.lasCtasMov = [];
        $scope.losTiposConf = [];
        $scope.detConf = {};
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        monedaSrvc.lstMonedas().then(function(d){
            $scope.lasMonedas = d;
        });

        $scope.getLstEmpresas = function(){
            empresaSrvc.lstEmpresas().then(function(d){
                for(var i = 0; i < d.length; i++){
                    d[i].dectc = parseInt(d[i].dectc);
                }
                $scope.lstEmpresas = d;
            });
        };

        $scope.editaEmpresa = function(obj, op){
            obj.idmoneda = obj.objMoneda.id;
            empresaSrvc.editRow(obj, op).then(function(){
                $scope.getLstEmpresas();
                $scope.laEmpresa = {};
            });
        };

        $scope.updEmpresa = function(data, id){
            data.id = id;
            empresaSrvc.editRow(data, 'u').then(function(){
                $scope.getLstEmpresas();
            });
        };

        $scope.delEmpresa = function(id){
            empresaSrvc.editRow({id:id}, 'd').then(function(){
                $scope.getLstEmpresas();
            });
        };

        $scope.getLstConfigConta = function(idempresa){
            empresaSrvc.lstConfigConta(idempresa).then(function(det){ $scope.lasConfsConta = det; });
        };

        $scope.getConfigConta = function (objEmpresa) {
            $scope.editando = true;
            $scope.etiqueta = objEmpresa;
            tipoConfigContaSrvc.lstTiposConfigConta().then(function(d){ $scope.losTiposConf = d; });
            cuentacSrvc.getByTipo(parseInt(objEmpresa.id), 0).then(function(d){ $scope.lasCtasMov = d; });
            $scope.getLstConfigConta(parseInt(objEmpresa.id));
        };

        $scope.addConfCont = function(obj){
            obj.idempresa = parseInt($scope.etiqueta.id);
            obj.idtipoconfig = parseInt(obj.objTipoConf.id);
            obj.idcuentac = parseInt(obj.objCuenta[0].id);
            empresaSrvc.editRow(obj, 'cc').then(function(){
                $scope.getLstConfigConta(parseInt($scope.etiqueta.id));
                $scope.detConf = {};
                $scope.searchcta = "";
            });
        };

        $scope.delConfConta = function(idconf){
            $confirm({text: '¿Seguro(a) de eliminar esta configuración?', title: 'Eliminar configuración contable', ok: 'Sí', cancel: 'No'}).then(function() {
                empresaSrvc.editRow({id:idconf}, 'dc').then(function(){ $scope.getLstConfigConta($scope.etiqueta.id); });
            });
        };

        $scope.getLstEmpresas();

    }]);

}());

