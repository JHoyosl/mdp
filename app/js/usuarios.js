define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es',], function(api,$,ko,moment){
    
    return {
        

        //Se crea un modelo para control

        usuariosModel: function(){
            
			//adduser form variables
            self.formMail = ko.observable("");
            self.fromIdLocal = ko.observable("");
			self.formTelefono = ko.observable("");
			self.formNombres = ko.observable();
			self.formApelldios = ko.observable("");
			self.formArea = ko.observable("");
			
			//edituser form variables
			self.editformMail = ko.observable("");
			self.editformTelefono = ko.observable("");
			self.editformNombres = ko.observable();
			self.editformApelldios = ko.observable("");
			self.editformId = ko.observable("");
			
			//filter form
			self.emailSearch = ko.observable("");
			self.nombreSearch = ko.observable("");
			self.apellidoSearch = ko.observable("");
			

			//lista de ususarios
			self.userList = ko.observableArray([]);
			
            self.addUserShow = function(){
                
				self.formMail("");
				self.fromIdLocal("");
				self.formNombres("");
				self.formApelldios("");
				self.selectedAreaUsuario("");
				self.selectedEmpresa("");
				self.formTelefono("");
				
				$( "#addUserModal" ).modal( "show");
            };

			//optionsvars
			self.areaUsuarioList = ko.observableArray();
			self.selectedAreaUsuario = ko.observable();
			
			self.centroList = ko.observableArray();
			self.selectedEmpresa = ko.observable();
			
			/////////////////////////
			//FUNCIONES GENERALES////
			/////////////////////////

			self.guardarUsuario = function(){
				
				var info = {};
				info.EMAIL = self.formMail();
				info.ID_INTERNO = self.fromIdLocal();
				info.TELEFONO = self.formTelefono();
				info.NOMBRES = self.formNombres();
				info.APELLIDOS = self.formApelldios();
				info.AREA = self.selectedAreaUsuario().id;
				info.EMPRESA = self.selectedEmpresa().id;
				
				api.ajaxCom("user","crearUsuario",info,function(response){
					
					if(response.data.status){
						
						$("#addUserModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
				});
			}
			
			self.guardarEditUsuario = function(){
				
				var info = {};
				info.EMAIL = self.editformMail();
				info.TELEFONO = self.editformTelefono();
				info.NOMBRES = self.editformNombres();
				info.APELLIDOS = self.editformApelldios();
				info.TELEFONO = self.editformTelefono();
				info.ID = self.editformId();
				
				api.ajaxCom("user","editarUsuario",info,function(response){
					
					if(response.data.status){
						
						alert("Usuario modificado exitosamente");
						self.loadUserTable();
						$("#editUserModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
				});
				
			}
			
			self.loadMaestros = function(){
				
				api.ajaxCom("maestros","getAreaUsuario",{},function(response){
					
					var tmpOpt = response.data.info;
					var opciones = [];
					
					for(var i = 0; i<tmpOpt.length; i++){
						
						opciones.push(new api.OptList(tmpOpt[i]["ID"],tmpOpt[i]["DESCRIPCION"]));
						
					}
					self.areaUsuarioList(opciones);
						
				});
				
				api.ajaxCom("maestros","getCentroList",{},function(response){
					
					var tmpOpt = response.data.info;
					var opciones = [];
					
					for(var i = 0; i<tmpOpt.length; i++){
						
						opciones.push(new api.OptList(tmpOpt[i]["ID"],tmpOpt[i]["DESCRIPCION"]));
						
					}
					self.centroList(opciones);
						
				});
								
				
				
			}
			self.editarUsuario = function(user){
				
				self.editformMail(user.EMAIL);
				self.editformNombres(user.NOMBRES);
				self.editformApelldios(user.APELLIDOS);
				self.editformTelefono(user.TELEFONO);
				self.editformId(user.ID);
				$( "#editUserModal" ).modal( "show");
		
				

            }
			self.loadUserTable = function(){
				
				var info = {};

				info.EMAIL = self.emailSearch();
				info.NOMBRES = self.nombreSearch();
				info.APELLIDOS = self.apellidoSearch();

				api.ajaxCom("user","getUserList",info,function(response){
					
					if(!response.data.status){
							
						alert("Error cargando Información");
						return;
					}
					
					var table = response.data.message;
					
                  
                    

					self.userList(table);
					

				});
				
			}
			
        },
        initialize: function(){

        	
        	ko.applyBindings(new this.usuariosModel(), document.getElementById("usuariosModel"));   
			
			self.loadUserTable();
			self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },

    }

})