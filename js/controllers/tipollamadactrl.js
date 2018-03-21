(function(){

    var tipollamadactrl = angular.module('cpm.tipollamadactrl', []);

    tipollamadactrl.controller('tipoLlamadaCtrl', ['$scope', 'authSrvc', '$route', 'tipoLlamadaSrvc', '$confirm', function($scope, authSrvc, $route, tipoLlamadaSrvc, $confirm){

        $scope.tipollamada = {descripcion: ''};
        $scope.tiposllamada = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstTiposLlamada = function(){ tipoLlamadaSrvc.lstTiposLlamada().then(function(d){ $scope.tiposllamada = d; }); };

        $scope.getTipoLlamada = function(idtipollamada){
            tipoLlamadaSrvc.getTipoLlamada(+idtipollamada).then(function(d){
                $scope.tipollamada = d[0];
                goTop();
            })
        };

        $scope.resetTipoLlamada = function(){
            $scope.tipollamada = {descripcion: ''};
        };

        $scope.addTipoLlamada = function(obj){
            tipoLlamadaSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstTiposLlamada();
                $scope.getTipoLlamada(d.lastid);
            });
        };

        $scope.updTipoLlamada = function(obj){
            tipoLlamadaSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstTiposLlamada();
                $scope.getTipoLlamada(obj.id);
            });
        };

        $scope.delTipoLlamada = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar el tipo de llamada ' + obj.descripcion + '?',
                title: 'Eliminar tipo de llamada', ok: 'Sí', cancel: 'No'}).then(function() {
                tipoLlamadaSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstTiposLlamada(); $scope.resetTipoLlamada(); });
            });
        };

        $scope.getLstTiposLlamada();
    }]);

}());
