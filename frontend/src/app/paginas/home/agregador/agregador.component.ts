import { Component, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-agregador',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  templateUrl: './agregador.component.html',
  styleUrl: './agregador.component.css'
})
export class AgregadorComponent {
  @Output() volverALista = new EventEmitter<void>();

  volver() {
    this.volverALista.emit();
  }
}