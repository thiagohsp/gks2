import React, { MouseEventHandler, PropsWithChildren, ReactElement, useMemo } from 'react';
import { Column, Row, TableInstance, TableOptions, useRowSelect, useSortBy, useTable } from "react-table";
import { Pagination } from '../../Pagination';
import { TableContainer, Td, Th, Tr, TrHead } from "./styles";
import { TablePagination } from './TablePagination';
import processColumns from "./utils";


export interface TableProperties<T extends Record<string, unknown>> extends TableOptions<T> {
    name: string;
    hideHeaders?: boolean;
    links: Array<{
        url: string;
        label: string;
        active: boolean;
    }>;
    onAdd?: (instance: TableInstance<T>) => MouseEventHandler
    onDelete?: (instance: TableInstance<T>) => MouseEventHandler
    onEdit?: (instance: TableInstance<T>) => MouseEventHandler
    onClick?: (row: Row<T>) => void
}


export type TableProps = {
    data: Array<any>;
    columns: Array<Column>;
    enableSorting?: boolean;
    hideHeaders?: boolean;
};

export function Table<T extends Record<string, unknown>>(props: PropsWithChildren<TableProperties<T>>): ReactElement {
    /* It's important that we're using React.useMemo here to ensure
     * that our data isn't recreated on every render. If we didn't use
     * React.useMemo, the table would think it was receiving new data on
     * every render and attempt to recalulate a lot of logic every single
     * time. Not cool!
     */

    const data = useMemo(() => props.data, [props.data]);
    const columns = useMemo(() => processColumns(props.columns, props.data), [
        props.columns,
        props.data,
    ]);

    const instance = useTable(
        // @ts-ignore
        { columns, data, disableSortBy: !props.enableSorting },
        useSortBy,
        useRowSelect
    );

    const {
        getTableProps,
        getTableBodyProps,
        headerGroups,
        rows,
        prepareRow,
        selectedFlatRows,
        state: { selectedRowIds }
    } = instance;

    return (
        <>
            <TableContainer {...getTableProps()}>
                {!props.hideHeaders && (
                    <thead>
                        {
                            // Loop over the header rows
                            headerGroups.map((headerGroup: any) => (
                                // Apply the header row props
                                <TrHead {...headerGroup.getHeaderGroupProps()}>
                                    {
                                        // Loop over the headers in each row
                                        headerGroup.headers.map((column: any) => (
                                            // Apply the header cell props
                                            <Th
                                                {...column.getHeaderProps(column.getSortByToggleProps())}
                                            >
                                                {
                                                    // Render the header
                                                    column.render("Header")
                                                }
                                            </Th>
                                        ))
                                    }
                                </TrHead>
                            ))
                        }
                    </thead>
                )}

                {/* Apply the table body props */}
                <tbody {...getTableBodyProps()}>
                    {
                        // Loop over the table rows
                        rows.map((row: any) => {
                            // Prepare the row for display
                            prepareRow(row);
                            return (
                                // Apply the row props
                                <Tr {...row.getRowProps()}>
                                    {
                                        // Loop over the rows cells
                                        row.cells.map((cell: any) => {
                                            // Apply the cell props
                                            return (
                                                <Td {...cell.getCellProps()}>
                                                    {
                                                        // Render the cell contents
                                                        cell.render("Cell")
                                                    }
                                                </Td>
                                            );
                                        })
                                    }
                                </Tr>
                            );
                        })
                    }
                </tbody>
            </TableContainer>
            { props.links && (
                <Pagination links={props.links} />
            )}
        </>
    );
}

Table.defaultProps = { enableSorting: false, hideHeaders: false };
