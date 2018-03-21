(function(){

    var transegcasoctrl = angular.module('cpm.transegcasoctrl', []);

    transegcasoctrl.controller('tranSegCasoCtrl', ['$scope', 'authSrvc', '$route', 'casoSrvc', '$confirm', 'toaster', '$filter', '$uibModal', function($scope, authSrvc, $route, casoSrvc, $confirm, toaster, $filter, $uibModal){

        $scope.permiso = {};
        $scope.caso = { };
        $scope.abiertos = [];
        $scope.usrdata = {};

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                $scope.usrdata = usrLogged;
            }
        });

        function setCasos(d){
            for(var i = 0; i < d.length; i++){
                d[i].fhapertura = moment(d[i].fhapertura).toDate();
            }
            return d;
        }

        $scope.getLstAbiertos = function(){
            casoSrvc.lstCasosAbiertos().then(function(d){
                $scope.abiertos = setCasos(d);
            });
        };

        $scope.getBckColor = function(visitado, horas){
            if(+visitado == 0){
                return +horas < 24 ? 'bckVerde' : 'bckRojo';
            }else{
                return +horas < 48 ? 'bckVerde' : 'bckAmarillo';
            }
        };

        $scope.openAddCaso = function(idcaso){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalAddCaso.html',
                controller: 'ModalAddCasoCtrl',
                resolve:{
                    idcaso: function(){ return +idcaso; },
                    usr: function(){return $scope.usrdata; }
                }
            });

            modalInstance.result.then(function(){
                $scope.getLstAbiertos();
            }, function(){ return 0; });
        };

        $scope.openBitacora = function(obj, index){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalBitacora.html',
                controller: 'ModalBitacoraCtrl',
                windowClass: 'app-modal-window',
                resolve:{
                    caso: function(){ return obj; },
                    usr: function(){return $scope.usrdata; }
                }
            });

            modalInstance.result.then(function(){
                //$scope.getLstAbiertos();
                //console.log('Lista de bitácoras cerrada...');
            }, function(){
                casoSrvc.getCaso(obj.id).then(function(d){
                    $scope.abiertos[index] = setCasos(d)[0];
                });
            });
        };

        $scope.openReqPartes = function(obj){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalReqParte.html',
                controller: 'ModalReqParteCtrl',
                windowClass: 'app-modal-window2',
                resolve:{
                    caso: function(){ return obj; },
                    usr: function(){return $scope.usrdata; }
                }
            });

            modalInstance.result.then(function(){
                console.log('Lista de requerimiento de partes cerrada...');
            }, function(){ return 0; });
        };

        $scope.openCierreCaso = function(obj){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalCierreCaso.html',
                controller: 'ModalCierreCasoCtrl',
                windowClass: 'app-modal-window3',
                resolve:{
                    caso: function(){ return obj; },
                    usr: function(){return $scope.usrdata; }
                }
            });

            modalInstance.result.then(function(){
                console.log('Pantalla de cierre de caso cerrada...');
                $scope.getLstAbiertos();
            }, function(){ return 0; });
        };

        $scope.getLstAbiertos();

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//

    transegcasoctrl.controller('ModalAddCasoCtrl', ['$scope', '$uibModalInstance', 'idcaso', 'casoSrvc', 'usr', 'estatusCasoSrvc', 'equipoSrvc', 'ubicacionSrvc', 'tipoLlamadaSrvc', 'toaster', function($scope, $uibModalInstance, idcaso, casoSrvc, usr, estatusCasoSrvc, equipoSrvc, ubicacionSrvc, tipoLlamadaSrvc, toaster){
        $scope.caso = { };
        $scope.estatus = [];
        $scope.equipos = [];
        $scope.ubicaciones = [];
        $scope.tiposllamada = [];

        estatusCasoSrvc.lstEstatus().then(function(d){ $scope.estatus = d; });
        equipoSrvc.lstEquipos().then(function(d){ $scope.equipos = d; });
        ubicacionSrvc.lstUbicaciones().then(function(d){ $scope.ubicaciones = d; });
        tipoLlamadaSrvc.lstTiposLlamada().then(function(d){ $scope.tiposllamada = d; });

        $scope.ok = function () { $uibModalInstance.close(); };

        $scope.cancel = function () { $uibModalInstance.dismiss('cancel'); };

        $scope.resetCaso = function(){
            $scope.caso = {
                id: 0, nocaso: '', idestatus: '1', idequipo: undefined, idubicacion: undefined, idtipollamada: undefined, fhapertura: moment().toDate(),
                idusuarioapertura: usr.uid, comentario: '', usuarioapertura: usr.usuario
            };
        };

        $scope.getCaso = function(idc){ casoSrvc.getCaso(idc).then(function(d){ $scope.caso = d[0]; }); };

        function prepCaso(obj){
            obj.idubicacion = obj.idubicacion != null && obj.idubicacion != undefined ? obj.idubicacion : '';
            obj.idtipollamada = obj.idtipollamada != null && obj.idtipollamada != undefined ? obj.idtipollamada : '';
            obj.comentario = obj.comentario != null && obj.comentario != undefined ? obj.comentario : '';
            obj.fhaperturastr = moment(obj.fhapertura).format('YYYY-MM-DD HH:mm:ss');
            return obj;
        }

        $scope.addCaso = function(obj){
            obj = prepCaso(obj);
            //console.log(obj); return;
            casoSrvc.editRow(obj, 'c').then(function(d){
                if(+d.lastid > 0){
                    $scope.ok();
                }else{
                    toaster.pop('error', 'Creación de caso', 'Hubo un error en la creación, por favor revise los datos ingresados.');
                }
            });
        };

        $scope.updCaso = function(obj){
            obj = prepCaso(obj);
            casoSrvc.editRow(obj, 'u').then(function(){
                $scope.ok();
            });
        };

        if(+idcaso > 0){
            $scope.getCaso(idcaso);
        }else{
            $scope.resetCaso();
        }

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//

    transegcasoctrl.controller('ModalBitacoraCtrl', ['$scope', '$uibModalInstance', 'caso', 'usr', 'toaster', 'casoSrvc', function($scope, $uibModalInstance, caso, usr, toaster, casoSrvc){
        $scope.caso = caso;
        $scope.bitacoras = [];
        $scope.bitacora = {};

        $scope.ok = function () { $uibModalInstance.close(); };

        $scope.cancel = function () { $uibModalInstance.dismiss('cancel'); };

        $scope.resetBitacora = function(){
            $scope.bitacora = {
                idcaso: +$scope.caso.id, fechahora: moment().toDate(), esvisita: 0, enviara: '', comentario:undefined, idusuario: usr.uid
            };
        };

        function setBitacoras(d){
            for(i = 0; i < d.length; i++){
                d[i].fechahora = moment(d[i].fechahora).toDate();
                d[i].esvisita = parseInt(d[i].esvisita);
            }
            return d;
        }

        $scope.loadBitacoras = function(idcaso){
            casoSrvc.lstBitacoras(+idcaso).then(function(d){ $scope.bitacoras = setBitacoras(d); });
            //console.log($scope.caso);
        };

        $scope.getBitacora = function(idbitacora){ casoSrvc.getBitacora(idbitacora).then(function(d){ $scope.bitacora = setBitacoras(d)[0]; }); };

        function prepBitacora(obj){
            obj.enviara = obj.enviara != null && obj.enviara != undefined ? obj.enviara : '';
            obj.esvisita = obj.esvisita != null && obj.esvisita != undefined ? obj.esvisita : 0;
            obj.fechahorastr = moment(obj.fechahora).format('YYYY-MM-DD HH:mm:ss');
            return obj;
        }

        $scope.addBitacora = function(obj){
            obj = prepBitacora(obj);
            casoSrvc.editRow(obj, 'cb').then(function(d){
                if(+d.lastid > 0){
                    $scope.loadBitacoras(+obj.idcaso);
                    $scope.getBitacora(+d.lastid);
                }else{
                    toaster.pop('error', 'Creación de bitácora', 'Hubo un error en la creación, por favor revise los datos ingresados.');
                }
            });
        };

        $scope.updBitacora = function(obj){
            obj = prepBitacora(obj);
            casoSrvc.editRow(obj, 'ub').then(function(){
                $scope.loadBitacoras(+obj.idcaso);
                $scope.getBitacora(+obj.id);
            });
        };

        $scope.loadBitacoras(+$scope.caso.id);
        $scope.resetBitacora();

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//

    transegcasoctrl.controller('ModalReqParteCtrl', ['$scope', '$uibModalInstance', 'caso', 'usr', 'toaster', 'casoSrvc', 'razonCambioSrvc', 'salidaSrvc', 'bodegaSrvc', '$confirm', 'parteSrvc', function($scope, $uibModalInstance, caso, usr, toaster, casoSrvc, razonCambioSrvc, salidaSrvc, bodegaSrvc, $confirm, parteSrvc){
        $scope.caso = caso;
        $scope.razonescambio = [];
        $scope.bodegas = [];
        $scope.salida = {};
        $scope.salidas = [];
        $scope.detsalida = {};
        $scope.detssalida = [];
        $scope.partes = [];

        $scope.loadRazonesCambio = function(){ razonCambioSrvc.lstRazonesCambio().then(function(d){ $scope.razonescambio = d; }); };
        $scope.loadBodegas = function(){ bodegaSrvc.lstBodegas().then(function(d){ $scope.bodegas = d; }); };
        $scope.loadPartes = function(){ parteSrvc.lstPartes().then(function(d){ $scope.partes = d; }); };

        $scope.ok = function () { $uibModalInstance.close(); };

        $scope.cancel = function () { $uibModalInstance.dismiss('cancel'); };

        function prepSalidas(d){
            for(var i = 0; i < d.length; i++){
                d[i].fecha = moment(d[i].fecha).toDate();
                d[i].fhcreacion = moment(d[i].fhcreacion).toDate();
                d[i].fhmodifica = moment(d[i].fhmodifica).isValid() ? moment(d[i].fhmodifica).toDate() : undefined;
            }
            return d;
        }

        function prepDetsSalida(d){
            for(var i = 0; i < d.length; i++){
                d[i].cantidad = parseFloat(parseFloat(d[i].cantidad).toFixed(4));
            }
            return d;
        }

        $scope.loadSalidas = function(idcaso){
            salidaSrvc.lstSalidasPorCaso(+idcaso).then(function(d){
                $scope.salidas = prepSalidas(d);
            });
        };

        $scope.loadDetalleSalida = function(idsalida){
            salidaSrvc.lstDetalleSalida(+idsalida).then(function(d){
                $scope.detssalida = prepDetsSalida(d);
            });
        };

        $scope.resetSalida = function(){
            $scope.salida = {
                idcaso: +$scope.caso.id, fecha: moment().toDate(), fhcreacion: moment().toDate(), idusrcrea: usr.uid, idrazoncambio: undefined
            };
            $scope.detsalida = {};
            $scope.detssalida = [];
        };

        $scope.getSalida = function(idsalida){
            $scope.salida = {};
            $scope.detsalida = {};
            $scope.detssalida = [];

            salidaSrvc.getSalida(+idsalida).then(function(d){
                $scope.salida = prepSalidas(d)[0];
                $scope.loadDetalleSalida(+idsalida);
                $scope.resetDetSalida();
            });

        };

        $scope.addSalida = function(obj){
            obj.fechastr = moment(obj.fecha).format('YYYY-MM-DD');
            obj.fhcreacionstr = moment(obj.fhcreacion).format('YYYY-MM-DD HH:mm:ss');
            salidaSrvc.editRow(obj, 'c').then(function(d){
                if(+d.lastid > 0){
                    $scope.loadSalidas(obj.idcaso);
                    $scope.getSalida(d.lastid);
                }else{
                    toaster.pop('error','Creación de salida', 'Hubo un error en la creación de la salida de bodega. Favor revisar datos ingresados.');
                }
            });
        };

        $scope.updSalida = function(obj){
            obj.fechastr = moment(obj.fecha).format('YYYY-MM-DD');
            obj.fhmodifica = moment().toDate();
            obj.fhmodificastr = moment(obj.fhmodifica).format('YYYY-MM-DD HH:mm:ss');
            obj.idusrmodifica = usr.uid;
            salidaSrvc.editRow(obj, 'u').then(function(d){
                $scope.loadSalidas(obj.idcaso);
                $scope.getSalida(obj.id);
            });
        };

        $scope.delSalida = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta salida de bodega?', title: 'Eliminar salida de bodega', ok: 'Sí', cancel: 'No'}).then(function() {
                salidaSrvc.editRow({id: obj.id}, 'd').then(function(){ $scope.loadSalidas($scope.caso.id); $scope.resetSalida(); });
            });
        };

        $scope.resetDetSalida = function(){
            $scope.detsalida = {
                idsalida: +$scope.salida.id, idbodega: undefined, idparte: undefined, cantidad: 1
            };
        };

        $scope.getDetalleSalida = function(iddetsalida){
            salidaSrvc.getDetalleSalida(+iddetsalida).then(function(d){
                $scope.detsalida = prepDetsSalida(d)[0];
            });
        };

        $scope.addDetSalida = function(obj){
            salidaSrvc.editRow(obj, 'cd').then(function(d){
                $scope.loadDetalleSalida(obj.idsalida);
                $scope.getDetalleSalida(d.lastid);
            });
        };

        $scope.updDetSalida = function(obj){
            salidaSrvc.editRow(obj, 'ud').then(function(d){
                $scope.loadDetalleSalida(obj.idsalida);
                $scope.getDetalleSalida(obj.id);
            });
        };

        $scope.delDetSalida = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta parte?', title: 'Eliminar detalle de requerimiento de parte', ok: 'Sí', cancel: 'No'}).then(function() {
                salidaSrvc.editRow({id: obj.id}, 'dd').then(function(){ $scope.loadDetalleSalida(obj.idsalida); $scope.resetDetSalida(); });
            });
        };

        $scope.loadRazonesCambio();
        $scope.loadBodegas();
        $scope.loadPartes();
        $scope.resetSalida();
        $scope.loadSalidas($scope.caso.id);

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//

    transegcasoctrl.controller('ModalCierreCasoCtrl', ['$scope', '$uibModalInstance', 'caso', 'casoSrvc', 'usr', 'toaster', 'tecnicoSrvc', 'fuenteCasoSrvc', 'tipoSolucionSrvc', '$confirm', function($scope, $uibModalInstance, caso, casoSrvc, usr, toaster, tecnicoSrvc, fuenteCasoSrvc, tipoSolucionSrvc, $confirm){
        $scope.caso = caso;
        $scope.tecnicos = [];
        $scope.tiposcaso = [];
        $scope.tipossolucion = [];

        tecnicoSrvc.lstTecnicos().then(function(d){ $scope.tecnicos = d; });
        fuenteCasoSrvc.lstAllTiposCaso().then(function(d){ $scope.tiposcaso = d; });
        tipoSolucionSrvc.lstTiposSolucion().then(function(d){ $scope.tipossolucion = d; });

        $scope.ok = function () { $uibModalInstance.close(); };

        $scope.cancel = function () { $uibModalInstance.dismiss('cancel'); };

        $scope.setCaso = function(){
            $scope.caso.idtecnico = undefined;
            $scope.caso.idtipocaso = undefined;
            $scope.caso.idtiposolucion = undefined;
            $scope.caso.serieequipouno = undefined;
            $scope.caso.serieequipodos = undefined;
            $scope.caso.serieequipotres = undefined;
            $scope.caso.comentariocierre = undefined;
            $scope.caso.fhcierre = moment().toDate();
            $scope.caso.usrcierre = usr.usuario;
        };

        function prepCaso(obj){
            obj.serieequipouno = obj.serieequipouno != null && obj.serieequipouno != undefined ? obj.serieequipouno : '';
            obj.serieequipodos = obj.serieequipodos != null && obj.serieequipodos != undefined ? obj.serieequipodos : '';
            obj.serieequipotres = obj.serieequipotres != null && obj.serieequipotres != undefined ? obj.serieequipotres : '';
            obj.comentariocierre = obj.comentariocierre != null && obj.comentariocierre != undefined ? obj.comentariocierre : '';
            obj.fhcierrestr = moment(obj.fhcierre).format('YYYY-MM-DD HH:mm:ss');
            obj.idusuariocierra = usr.uid;
            return obj;
        }

        $scope.cerrarCaso = function(obj){
            $confirm({text: '¿Seguro(a) de cerrar el caso ' + obj.nocaso + '?', title: 'Cierre de caso', ok: 'Sí', cancel: 'No'}).then(function() {
                obj = prepCaso(obj);
                //console.log(obj);
                casoSrvc.editRow(obj, 'cierre').then(function(){ $scope.ok(); });
            });
        };

        $scope.setCaso();

    }]);

}());
