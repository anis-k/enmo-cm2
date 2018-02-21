import { NgModule } from '@angular/core';
import { DndModule }                            from 'ng2-dnd';
import {
    MatSelectModule,
    MatCheckboxModule,
    MatSlideToggleModule,
    MatInputModule,
    MatTooltipModule,
    MatTabsModule,
    MatSidenavModule,
    MatButtonModule,
    MatCardModule,
    MatButtonToggleModule,
    MatProgressSpinnerModule,
    MatToolbarModule,
    MatMenuModule,
    MatGridListModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatPaginatorIntl,
    MatDatepickerModule,
    MatNativeDateModule,
    MatExpansionModule,
    MatAutocompleteModule,
    MatSnackBar,
    MatSnackBarModule,
    MatIcon,
    MatIconModule,
    MatDialogActions,
    MatDialogModule,
    MatListModule,
    MatChipsModule,
    MatStepperModule,
    MatRadioModule,
    MatSliderModule
} from '@angular/material';

import { CdkTableModule } from '@angular/cdk/table';
import { getFrenchPaginatorIntl } from './french-paginator-intl';

@NgModule({
    imports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatDatepickerModule,
        MatNativeDateModule,
        MatExpansionModule,
        MatAutocompleteModule,
        MatSnackBarModule,
        MatIconModule,
        MatDialogModule,
        MatListModule,
        MatChipsModule,
        MatStepperModule,
        MatRadioModule,
        MatSliderModule,
        DndModule.forRoot()
    ],
    exports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatDatepickerModule,
        MatNativeDateModule,
        MatExpansionModule,
        MatAutocompleteModule,
        MatSnackBarModule,
        MatIconModule,
        MatDialogModule,
        MatListModule,
        MatChipsModule,
        MatStepperModule,
        MatRadioModule,
        MatSliderModule,
        DndModule
    ],
    providers: [
        { provide: MatPaginatorIntl, useValue: getFrenchPaginatorIntl() }
    ]
})
export class AppMaterialModule { }
