import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

@Component({
  selector: 'app-icon-button',
  templateUrl: './icon-button.component.html',
  styleUrls: ['./icon-button.component.css']
})
export class IconButtonComponent implements OnInit {

  @Output() click = new EventEmitter<any>();
  @Input() icon: string = '';

  constructor() { }

  ngOnInit() {
  }

  onClick(): void {
    this.click.emit(true);
  }
}
