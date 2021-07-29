import React, { useEffect, useMemo, useRef, useState } from "react";
import { Table } from "../../Shared/Components/__Table";
import { Layout } from "../../Shared/Layout";
import { FiList, FiDownload, FiTrash } from 'react-icons/fi'
import { format, parseISO } from "date-fns";
import { Column } from "react-table";
import { InertiaLink } from "@inertiajs/inertia-react";


type Batch = {
    id: string;
    code: string;
    email: string;
    total_value: number;
    max_bill_value: number;
    status: string;
    created_at: Date;
    updated_at: Date;
}

interface IPageProps {
    data: Batch[];
}

const Index: React.FC<IPageProps> = (props) => {

    const columns: Array<Column<Batch>> = useMemo(() => [
        {
            Header: 'Código',
            accessor: 'code',

        },
        {
            Header: 'Data',
            accessor: (d: any) => {
                return format(parseISO(d.created_at), "dd/MM/yyyy")
            }
        },
        {
            accessor: 'total_value',
            Cell: (props: any) => <div style={{ textAlign: "right" }}>
                {Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(props.value))}</div>,
            Header: () => (
                <div style={{ textAlign: "right" }}>Valor</div>)

        },
        {
            accessor: 'max_bill_value',
            Cell: (props: any) => <div style={{ textAlign: "right" }}>
                {Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(props.value))}</div>,
            Header: () => (
                <div style={{ textAlign: "right" }}>Valor por Boleto</div>)

        },
        {
            Header: 'Status',
            accessor: 'status',
            Cell: (props: any) => <div>{props.value === 'A' ? 'ABERTO' : 'CANCELADO'}</div>,

        },
        {
            Header: 'E-mail',
            accessor: 'email',

        },
        {
            Header: 'Ações',
            accessor: 'id',
            Cell: (props: any) =>
                <>
                    <InertiaLink
                        href={`/lotes/${props.value}/contas_a_recebers`}
                        as="button"
                        type="button"
                        className="bg-green-500 hover:bg-green-700 text-white text-xs font-bold mx-2 py-2 px-4 rounded-full inline-flex items-center">
                        <FiList size={16}></FiList>
                        <span className="ml-2">Boletos</span>
                    </InertiaLink>

                    <a
                        href={`/file?d=${props.value}`}
                        className="bg-green-500 hover:bg-green-700 text-white text-xs font-bold mx-2 py-2 px-4 rounded-full inline-flex items-center">
                        <FiDownload size={16} color={"#fff"}></FiDownload>
                        <span className="ml-2">Download</span>
                    </a>

                    <InertiaLink
                        href={`/lotes/${props.value}`}
                        method="DELETE"
                        as="button"
                        type="button"
                        only={['data']}
                        className="bg-red-500 hover:bg-red-700 text-white text-xs font-bold mx-2 py-2 px-4 rounded-full inline-flex items-center">
                        <FiTrash size={16}></FiTrash>
                    </InertiaLink>
                </>
        },
    ], []);

    const title = 'Lista de Lotes Gerados';

    return (
        <Layout title={title}>
            <div className="flex items-center justify-between mb-6">
                <h1 className="mb-1 text-2xl font-bold">{title}</h1>
            </div>

            <div className="bg-white rounded shadow">
                <Table<Batch>
                    //columns={columns}
                    name="batches-table"
                    columns={columns}
                    data={props.data}
                />
            </div>


        </Layout>
    );
};

export default Index;





