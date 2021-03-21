import React, { PropsWithChildren, ReactElement, useCallback, useMemo } from 'react'
import Select from '../../Select';
import ReactSelect from 'react-select';
import { TableInstance } from 'react-table'

import { Button, Container } from './styles';

const rowsPerPageOptions = [5, 10, 25, 50]

// a bit of a type hack to keep OverridableComponent working as desired
export function TablePagination<T extends Record<string, unknown>>({
    instance,
}: PropsWithChildren<{ instance: TableInstance<T> }>): ReactElement | null {
    const {
        state: { pageIndex, pageSize, rowCount = instance.rows.length },
        canPreviousPage,
        canNextPage,
        pageOptions,
        pageCount,
        gotoPage,
        nextPage,
        previousPage,
        setPageSize
    } = instance

    const handleChangePage = useCallback(
        (event: React.MouseEvent<HTMLButtonElement, MouseEvent> | null, newPage: number) => {
            if (newPage === pageIndex + 1) {
                nextPage()
            } else if (newPage === pageIndex - 1) {
                previousPage()
            } else {
                gotoPage(newPage)
            }
        },
        [gotoPage, nextPage, pageIndex, previousPage]
    )

    const pageSizeOptions = useMemo(() => {
        return [
            { value: 10, label: "Mostrar 10 registros" },
            { value: 20, label: "Mostrar 20 registros" },
            { value: 30, label: "Mostrar 30 registros" },
            { value: 40, label: "Mostrar 40 registros" },
            { value: 50, label: "Mostrar 50 registros" },
            { value: 100, label: "Mostrar 100 registros" }]
    }, []);

    const onChangeRowsPerPage = useCallback(
        (e) => {
            setPageSize(Number(e.target.value))
        },
        [setPageSize]
    );

    const filterPages = useCallback((visiblePages: Array<number>, totalPages: number) => {
        return visiblePages.filter(page => page <= totalPages);
    }, []);

    const getVisiblePages = useCallback((page: number, total: number) => {
        if (total < 7) {
            return filterPages([1, 2, 3, 4, 5, 6], total);
        } else {
            if (page % 5 >= 0 && page > 4 && page + 2 < total) {
                return [1, -1, page - 1, page, page + 1, -2, total];
            } else if (page % 5 >= 0 && page > 4 && page + 2 >= total) {
                return [1, -1, total - 3, total - 2, total - 1, total];
            } else {
                return [1, 2, 3, 4, 5, -2, total];
            }
        }
    }, []);

    return rowCount ? (

        <>

            <Container>

                <Button onClick={() => previousPage()} disabled={!canPreviousPage}>
                    {'Anterior'}
                </Button>{' '}

                {getVisiblePages(pageIndex + 1, pageOptions.length).map(page => (
                    <Button key={page} onClick={() => gotoPage(page - 1)} active={page === pageIndex + 1}>
                        {page > 0 ? page : '...'}
                    </Button>
                ))}

                <Button onClick={() => nextPage()} disabled={!canNextPage}>
                    {'Próxima'}
                </Button>
                <div style={{ flex: 1 }}>
                    <ReactSelect
                        options={pageSizeOptions}

                        onChange={e => {
                            setPageSize(e ? Number(e.value) : 10)
                        }}
                    />
                </div>

            </Container>
        </>) : null
}
