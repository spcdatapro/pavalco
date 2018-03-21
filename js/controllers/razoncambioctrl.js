(function(){

    var razoncambioctrl = angular.module('cpm.razoncambioctrl', []);

    razoncambioctrl.controller('razonCambioCtrl', ['$scope', 'authSrvc', '$route', 'razonCambioSrvc', '$confirm', function($scope, authSrvc, $route, razonCambioSrvc, $confirm){

        $scope.razoncambio = {descripcion: ''};
        $scope.razonescambio = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstRazonesCambio = function(){ razonCambioSrvc.lstRazonesCambio().then(function(d){ $scope.razonescambio = d; }); };

        $scope.getRazonCambio = function(idrazoncambio){
            razonCambioSrvc.getRazonCambio(+idrazoncambio).then(function(d){
                $scope.razoncambio = d[0];
                goTop();
            })
        };

        $scope.resetRazonCambio = function(){
            $scope.razoncambio = {descripcion: ''};
        };

        $scope.addRazonCambio = function(obj){
            razonCambioSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstRazonesCambio();
                $scope.getRazonCambio(d.lastid);
            });
        };

        $scope.updRazonCambio = function(obj){
            razonCambioSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstRazonesCambio();
                $scope.getRazonCambio(obj.id);
            });
        };

        $scope.delRazonCambio = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar el tipo de llamada ' + obj.descripcion + '?',
                title: 'Eliminar tipo de llamada', ok: 'Sí', cancel: 'No'}).then(function() {
                razonCambioSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstRazonesCambio(); $scope.resetRazonCambio(); });
            });
        };

        $scope.getLstRazonesCambio();
    }]);

}());