import { parseISO, format, parseJSON } from 'date-fns';
import React from 'react';
/**
 * If data in a column is numeric and no custom Cell render function
 * is provided then add a custom Cell render function to format the numbers
 */
export function processColumns(columns: Array<any>, data: Array<any>) {
    let columnIndex = 0;
    let columnNames = columns.map((item) => item.accessor);

    for (let td in data[0]) {
        /* Valida se o campo esta na lista de colunas */
        if (columnNames.includes(td)) {
            columnIndex = columnNames.indexOf(td);
            //console.log(`${td}: ${data[0][td]}`);

            if ((typeof data[0][td] === 'number' || data[0][td] instanceof Number) && !("Cell" in columns[columnIndex])) {
                if (columns[columnIndex]['type'] === 'currency') {
                    columns[columnIndex]["Cell"] = (props: any) => (
                        <div style={{ textAlign: "right" }}>{props.value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</div>
                    );
                }
            }

            if (columns[columnIndex]['type'] === 'date') {
                columns[columnIndex]["Cell"] = (props: any) => (
                    <>{format(parseISO(props.value), "dd/MM/yyyy")}</>
                );
            }

        }
        if (!columns[columnIndex]) break;
    }
    return columns;
}

export default processColumns;
