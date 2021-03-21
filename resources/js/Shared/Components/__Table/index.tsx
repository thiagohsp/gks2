import React, { MouseEventHandler, PropsWithChildren, ReactElement, useMemo } from 'react';
import { Column, Row, TableInstance, TableOptions, usePagination, useRowSelect, useSortBy, useTable } from "react-table";
import { Pagination } from '../../Pagination';
import { TableContainer, Td, Th, Tr, TrHead } from "./styles";
import { TablePagination } from './TablePagination';
import processColumns from "./utils";


export interface TableProperties<T extends Record<string, unknown>> extends TableOptions<T> {
    name: string;
    hideHeaders?: boolean;
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
        usePagination,
        useRowSelect,
    );

    const {
        getTableProps,
        getTableBodyProps,
        headerGroups,
        rows,
        prepareRow,
        page, // Instead of using 'rows', we'll use page,
        state: { selectedRowIds },
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

                <tbody {...getTableBodyProps()}>
                    {page.map((row, i) => {
                        prepareRow(row)
                        return (
                            <Tr {...row.getRowProps()}>
                                {row.cells.map(cell => {
                                    return <Td {...cell.getCellProps()}>{cell.render('Cell')}</Td>
                                })}
                            </Tr>
                        )
                    })}
                </tbody>
            </TableContainer>

            <TablePagination instance={instance} />
        </>
    );
}

Table.defaultProps = { enableSorting: false, hideHeaders: false };
