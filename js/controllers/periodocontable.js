(function(){

    var periodocontctrl = angular.module('cpm.periodocontctrl', ['cpm.pcontsrvc']);

    periodocontctrl.controller('periodoContableCtrl', ['$scope', 'periodoContableSrvc', 'toaster', '$confirm', 'authSrvc', '$route', function($scope, periodoContableSrvc, toaster, $confirm, authSrvc, $route){

        $scope.elPeriodo = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), abierto: 0};
        $scope.losPeriodos = [];
        $scope.permiso = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){ authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; }); }
        });

        function procData(data){
            for(var i = 0; i < data.length; i++){
                data[i].del = moment(data[i].del).toDate();
                data[i].al = moment(data[i].al).toDate();
                data[i].abierto = parseInt(data[i].abierto);
            }
            return data;
        }

        $scope.getLstPeriodos = function(){
            periodoContableSrvc.lstPeriodosCont().then(function(d){
                $scope.losPeriodos = procData(d);
            });
        };

        function setData(obj){
            obj.delstr = obj.del !== null && obj.del !== undefined ? moment(obj.del).format('YYYY-MM-DD') : '';
            obj.alstr = obj.al !== null && obj.al !== undefined ? moment(obj.al).format('YYYY-MM-DD') : '';
            obj.abierto = obj.abierto != null && obj.abierto != undefined ? (+obj.abierto == 1 ? 0 : 1) : 0;
            return obj;
        }

        $scope.addPeriodo = function(obj){
            obj = setData(obj);
            if(moment(obj.del).isBefore(obj.al)){
                periodoContableSrvc.editRow(obj, 'c').then(function(){
                    $scope.getLstPeriodos();
                    $scope.elPeriodo = {del: moment().startOf('month').toDate(), al: moment().endOf('month').toDate(), abierto: 0};
                });
            }else{
                toaster.pop({ type: 'error', title: 'Error en las fechas.',
                    body: 'La fecha inicial no puede ser mayor a la fecha final.', timeout: 7000 });
                $scope.elPeriodo.al = moment(obj.del).endOf('month').toDate();
            }


        };

        $scope.updPeriodo = function(data){
            data = setData(data);
            periodoContableSrvc.editRow(data, 'u').then(function(){
                $scope.getLstPeriodos();
            });
        };

        $scope.delPeriodo = function(id){
            $confirm({text: '¿Seguro(a) de eliminar este período de trabajo?', title: 'Eliminar período de trabajo', ok: 'Sí', cancel: 'No'}).then(function() {
                periodoContableSrvc.editRow({id:id}, 'd').then(function(){ $scope.getLstPeriodos(); });
            });
        };

        $scope.getLstPeriodos();
    }]);

}());
