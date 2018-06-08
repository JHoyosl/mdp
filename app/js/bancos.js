define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es',], function(api,$,ko,moment){
    
    return {
		
		bancosModel: function(){
			
			

			//lista de bancos
			self.bancosList = ko.observableArray([]);
			
			//addbancos form variables
            self.formAddIdBanco = ko.observable("").extend({ uppercase: true });
            self.formAddNombreBanco = ko.observable("").extend({ uppercase: true });
			self.formAddCodComp = ko.observable("").extend({ uppercase: true });
			self.formAddMoneda = ko.observable();
			self.formAddTelefono = ko.observable("").extend({ uppercase: true });
			self.formAddContacto = ko.observable("").extend({ uppercase: true });
			self.formAddEmail = ko.observable("").extend({ uppercase: true });
			self.formAddUrlPortal = ko.observable("").extend({ uppercase: true });
			self.formAddComisionTx = ko.observable("").extend({ uppercase: true });
			self.formAddCanalTx = ko.observable("").extend({ uppercase: true });

			//edit bancos form variables
            self.formEditIdBanco = ko.observable("").extend({ uppercase: true });
            self.formEditNombreBanco = ko.observable("").extend({ uppercase: true });
			self.formEditCodComp = ko.observable("").extend({ uppercase: true });
			self.formEditMoneda = ko.observable();
			self.formEditPortal = ko.observable(true);
			
			
			//search bancos form variables
            self.CodCompSearch = ko.observable("").extend({ uppercase: true });
            self.nombreSearch = ko.observable("").extend({ uppercase: true });
			self.nitSearch = ko.observable("").extend({ uppercase: true });
			
			//options variables 
			self.monedaList = ko.observableArray(["COP","USD"]);
			
			
			self.guardarEditarBancos = function(){
				
				var info = {};
				
				info.bancoId = self.formEditIdBanco();
				info.nombre = self.formEditNombreBanco();
				info.codComp = self.formEditCodComp();
				info.moneda = self.formEditMoneda();
				info.portal = self.formEditPortal();		
				
				console.log(info);

				api.ajaxCom("bancos","editarBanco",info,function(response){
					
					console.log(response);
					if(response.data.status){
						
						$("#editBancoModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadBancosTable();
				});
				
			}
			
			self.gaurdarBanco = function(){
				
				var info = {};
				
				info.bancoId = self.formAddIdBanco();
				info.nombre = self.formAddNombreBanco();
				info.codComp = self.formAddCodComp();
				info.moneda = self.formAddMoneda();
				info.telefono = self.formAddTelefono();	
				info.contacto = self.formAddContacto();	
				info.email = self.formAddEmail();	
				info.portal = self.formAddUrlPortal();	
				info.comision = self.formAddComisionTx();	
				info.canal = self.formAddCanalTx();		
				
				api.ajaxCom("bancos","crearBanco",info,function(response){
					
					if(response.data.status){
						
						alert("Registro agregado");
						$("#addBancoModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadBancosTable();
				});
			}
			
			self.addBancoShow = function(){
                
				self.formAddIdBanco(banco.BANCO_ID);
				self.formAddNombreBanco(banco.NOMBRE);
				self.formAddCodComp(banco.COD_COMP);
				self.formAddMoneda(banco.MONEDA);
				self.formAddTelefono(banco.TELEFONO);	
				self.formAddContacto(banco.CONTACTO);	
				self.formAddEmail(banco.EMAIL);	
				self.formAddUrlPortal(banco.PORTAL);	
				self.formAddComisionTx(banco.COMISION);	
				self.formAddCanalTx(banco.CANALES);	
			

				$( "#addBancoModal" ).modal( "show");
            };
			
			self.editarBanco = function(banco){
				
				self.formEditIdBanco(banco.BANCO_ID);
				self.formEditNombreBanco(banco.NOMBRE);
				self.formEditCodComp(banco.COD_COMP);
				self.formEditMoneda(banco.MONEDA);
				self.formEditPortal(banco.PORTAL);	
					

				$( "#editBancoModal" ).modal( "show");
			}
			
			
			self.loadBancosTable = function(){
				
				var info = {};

				info.codComp = self.CodCompSearch();
				info.nombre = self.nombreSearch();
				info.nit = self.nitSearch();

				api.ajaxCom("bancos","getBancosList",info,function(response){
					
					if(!response.data.status){
							
						alert("Error cargando Información");
						return;
					}
					
					var table = response.data.message;

					self.bancosList(table);
					
				});
				
			}
			 
		},
		initialize: function(){

        	
        	ko.applyBindings(new this.bancosModel(), document.getElementById("bancosModel"));   
			
			self.loadBancosTable();
			// self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})