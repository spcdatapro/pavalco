(function(){

    var fuentecasoctrl = angular.module('cpm.fuentecasoctrl', []);

    fuentecasoctrl.controller('fuenteCasoCtrl', ['$scope', 'authSrvc', '$route', 'fuenteCasoSrvc', '$confirm', function($scope, authSrvc, $route, fuenteCasoSrvc, $confirm){

        $scope.fuente = {descripcion: ''};
        $scope.fuentes = [];
        $scope.tipocaso = {};
        $scope.tiposcaso = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
            }
        });

        $scope.getLstFuentes = function(){ fuenteCasoSrvc.lstFuentesCaso().then(function(d){ $scope.fuentes = d; }); };

        $scope.getFuente = function(idfuentecaso){
            fuenteCasoSrvc.getFuenteCaso(+idfuentecaso).then(function(d){
                $scope.fuente = d[0];
                $scope.resetTipoCaso();
                $scope.getLstTiposCaso(idfuentecaso);
                goTop();
            })
        };

        $scope.resetFuente = function(){
            $scope.fuente = {descripcion: ''};
            $scope.tipocaso = {};
            $scope.tiposcaso = [];
        };

        $scope.addFuente = function(obj){
            fuenteCasoSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstFuentes();
                $scope.getFuente(d.lastid);
            });
        };

        $scope.updFuente = function(obj){
            fuenteCasoSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstFuentes();
                $scope.getFuente(obj.id);
            });
        };

        $scope.delFuente = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar la fuente ' + obj.descripcion + '? (Esto también eliminará los tipos de caso asociados a esta fuente)',
                title: 'Eliminar fuente', ok: 'Sí', cancel: 'No'}).then(function() {
                fuenteCasoSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.getLstFuentes(); $scope.resetFuente(); });
            });
        };

        $scope.getLstTiposCaso = function(idfuentecaso){
            fuenteCasoSrvc.lstTiposCasoByFuente(+idfuentecaso).then(function(d){
                $scope.tiposcaso = d;
            });
        };

        $scope.getTipoCaso = function(idtipocaso){
            fuenteCasoSrvc.getTipoCaso(+idtipocaso).then(function(d){
                $scope.tipocaso = d[0]
                goTop();
            });
        };

        $scope.resetTipoCaso = function(){
            $scope.tipocaso = {
                idfuentecaso: $scope.fuente != null && $scope.fuente != undefined ?($scope.fuente.id > 0 ? $scope.fuente.id : 0) : 0,
                descripcion: '',
                mostrar: '1'
            };
        };

        $scope.addTipoCaso = function(obj){
            fuenteCasoSrvc.editRow(obj, 'cd').then(function(d){
                $scope.getLstTiposCaso(obj.idfuentecaso);
                $scope.getTipoCaso(d.lastid);
            });
        };

        $scope.updTipoCaso = function(obj){
            fuenteCasoSrvc.editRow(obj, 'ud').then(function(d){
                $scope.getLstTiposCaso(obj.idfuentecaso);
                $scope.getTipoCaso(obj.id);
            });
        };

        $scope.delFuente = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar ' + obj.fuentecaso + ' - ' + obj.descripcion + '?',
                title: 'Eliminar tipo de caso', ok: 'Sí', cancel: 'No'}).then(function() {
                fuenteCasoSrvc.editRow({id: obj.id}, 'dd').then(function(){ $scope.getLstTiposCaso(obj.idfuentecaso); $scope.resetTipoCaso(); });
            });
        };

        $scope.getLstFuentes();
    }]);

}());

