import { Component, Input, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatMenuModule } from '@angular/material/menu'; 
import { MatIconModule } from '@angular/material/icon';
import { NotificacionesService } from '../servicios/notificaciones.service'; // Ajustar la ruta del servicio

@Component({
  selector: 'app-cabecera',
  standalone: true,
  imports: [
    MatToolbarModule,
    MatMenuModule,
    MatIconModule,
    CommonModule
  ],
  templateUrl: './cabecera.component.html',
  styleUrl: './cabecera.component.css'
})
export class CabeceraComponent implements OnInit, OnDestroy {
  @Input() nombreUsuario: string = '';
  mostrarAdministracion: boolean = false;
  permisoUsuario: string = "1";
  nuevasSolicitudes: number = 0;

  constructor(private notificacionesService: NotificacionesService) {}

  ngOnInit() {
    this.nombreUsuario = localStorage.getItem('usuario') || "usuario";

    if (this.nombreUsuario) {
      this.nombreUsuario = this.nombreUsuario;

      this.permisoUsuario = localStorage.getItem('permiso') || "1";

      if (this.permisoUsuario == "2") {
        console.log(localStorage.getItem('permiso'));
        this.mostrarAdministracion = true;
      } else {
        this.mostrarAdministracion = false;
      }

      this.notificacionesService.nuevasSolicitudes$.subscribe(nuevasSolicitudes => {
        this.nuevasSolicitudes = nuevasSolicitudes;
      });

      this.notificacionesService.actualizarNumeroSolicitudes();
      this.notificacionesService.iniciarRevisionPeriodica(); // Iniciar revisión periódica
    }
  }

  ngOnDestroy() {
    this.notificacionesService.detenerRevisionPeriodica(); // Detener revisión periódica al destruir el componente
  }

  cerrarSesion() {
    localStorage.clear();
    window.location.href = '/';
  }

  verSolicitudes() {
    window.location.href = '/perfil';
  }
}