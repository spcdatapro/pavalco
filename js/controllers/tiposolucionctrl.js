(function(){

    var tiposolucionctrl = angular.module('cpm.tiposolucionctrl', []);

    tiposolucionctrl.controller('tipoSolucionCtrl', ['$scope', 'authSrvc', '$route', 'tipoSolucionSrvc', '$confirm', function($scope, authSrvc, $route, tipoSolucionSrvc, $confirm){

        $scope.tiposolucion = {descripcion: ''};
        $scope.tipossolucion = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstTiposSolucion = function(){ tipoSolucionSrvc.lstTiposSolucion().then(function(d){ $scope.tipossolucion = d; }); };

        $scope.getTipoSolucion = function(idtiposolucion){
            tipoSolucionSrvc.getTipoSolucion(+idtiposolucion).then(function(d){
                $scope.tiposolucion = d[0];
                goTop();
            })
        };

        $scope.resetTipoSolucion = function(){
            $scope.tiposolucion = {descripcion: ''};
        };

        $scope.addTipoSolucion = function(obj){
            tipoSolucionSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstTiposSolucion();
                $scope.getTipoSolucion(d.lastid);
            });
        };

        $scope.updTipoSolucion = function(obj){
            tipoSolucionSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstTiposSolucion();
                $scope.getTipoSolucion(obj.id);
            });
        };

        $scope.delTipoSolucion = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar el tipo de solucion ' + obj.descripcion + '?',
                title: 'Eliminar tipo de solucion', ok: 'Sí', cancel: 'No'}).then(function() {
                tipoSolucionSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstTiposSolucion(); $scope.resetTipoSolucion(); });
            });
        };

        $scope.getLstTiposSolucion();
    }]);

}());