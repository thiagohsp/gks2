import React, { useEffect, useMemo, useRef, useState } from "react";
import { Table } from "../../Shared/Components/__Table";
import { Layout } from "../../Shared/Layout";
import { Pagination } from "../../Shared/Pagination";
import { FiFilter, FiPlus, FiSearch } from 'react-icons/fi'
import AdvancedSearch from "./AdvancedSearch";
import { FormHandles, SubmitHandler } from "@unform/core";
import axios from "axios";
import { Form } from "@unform/web";
import Select from '../../Shared/Components/Select';
import Input from '../../Shared/Components/Input';
import DatePicker from '../../Shared/Components/DatePicker';
import NumberInput from '../../Shared/Components/NumberInput';
import { format, parseISO } from "date-fns";
import { Column } from "react-table";

interface ICustomer {
    id: string;
    document_number: string;
    social_name: string;
    adress_street: string;
    adress_number: string;
    adress_complement: string;
    adress_district: string;
    adress_zipcode: string;
    adress_city: string;
    adress_state: string;
    adress_country: string;
    email: string;
    customer_balance: number;
}

type Invoice = {
    id: string;
    date: string;
    number: number;
    key: string;
    serie: string;
    cfop: string;
    value: number;
    balance: number;
    agent?: string;
    agent_2?: string;
    operation?: string;
    status: string;
    last_letter: string;
    customer: ICustomer;
    falta_faturar: number;
}

interface IPageProps {
    invoices: Invoice[];
    links: Array<{
        url: string;
        label: string;
        active: boolean;
    }>;
}

interface IAgente {
    label: string;
    value: string;
}

interface IContaCorrente {
    id: string;
    codigo_conta_corrente_maino: string;
    bank_number: string;
    bank_name: string;
    agency: string;
    account: string;
    allow_pjbank_bills: boolean;
    active: boolean;
    value?: string;
    label?: string;
}

interface ICliente {
    id: string;
    social_name: string;
    is_active: boolean;
    value?: string;
    label?: string;
}

interface FormData {
    customer?: string;
    data_ini?: Date;
    data_fin?: Date;
    nf_ini?: number;
    nf_fin?: number;
    agente?: string;
    agente2?: string;
    saldo_ini?: number;
    saldo_fin?: number;
}

const Index: React.FC<IPageProps> = (props) => {

    const [invoices, setInvoices] = useState<Invoice[]>([]);
    const [clientes, setClientes] = useState<ICliente[]>([]);
    const [agents, setAgents] = useState<IAgente[]>([]);
    const [agents2, setAgents2] = useState<IAgente[]>([]);
    const [contasCorrentes, setContasCorrentes] = useState<IContaCorrente[]>([]);
    const formFiltroRef = useRef<FormHandles>(null);
    const formLoteRef = useRef<FormHandles>(null);

    useEffect(() => {
        const { invoices } = props;

        const distinctAgents = Array.from(new Set(invoices.map(x => x.agent))).map(
            (item) => {
                return {
                    label: item || "",
                    value: item || ""
                }
            }
        );

        setAgents(distinctAgents);

        const distinctAgents2 = Array.from(new Set(invoices.map(x => x.agent_2))).map(
            (item) => {
                return {
                    label: item || "",
                    value: item || ""
                }
            }
        );

        setAgents2(distinctAgents2);

        setInvoices(invoices);
    }, []);

    useEffect(() => {
        async function loadCustomers() {
            await axios.get<ICliente[]>('api/customers')
                .then((response) => {
                    const mappedData = response?.data?.map((data) => {
                        return {
                            ...data,
                            value: data.id,
                            label: data.social_name
                        }
                    })
                    setClientes(mappedData.filter((item) => item.is_active));
                });
        }

        loadCustomers();

    }, []);

    useEffect(() => {
        axios.get<IContaCorrente[]>('api/accounts')
            .then((response) => {
                const mappedData = response?.data?.filter((item) => {
                    return (item.active && item.allow_pjbank_bills)
                }).map((data) => {
                    return {
                        ...data,
                        value: data.id,
                        label: `${data.bank_number} - ${data.bank_name.toUpperCase()} | ${data.agency} | ${data.account} | ${data.label}`
                    }
                })
                setContasCorrentes(mappedData);
            });
    }, []);

    const handleSubmit: SubmitHandler<FormData> = data => {
        console.log(data);
        axios.get('api/invoices', {
            params: {
                customer_id: data.customer || null,
                data_ini: data.data_ini || null,
                data_fin: data.data_fin || null,
                nf_ini: data.nf_ini || null,
                nf_fin: data.nf_fin || null,
                saldo_ini: Number(data.saldo_ini),
            }
        }).then((response) => {
            setInvoices(response.data);
        });
    }

    const handleSubmitLote: SubmitHandler<FormData> = formData => {
        const requestData = {
            ...formData,
            notas_fiscais: selectedRows
        }

        axios.post('/api/batch', requestData).then((response) =>
            console.log('Data: ' + response.data)
        );
    }

    const columns: Array<Column<Invoice>> = useMemo(() => [
        {
            Header: 'Número',
            accessor: 'number'
        },
        {
            Header: 'Data',
            accessor: (d: any) => {
                return format(parseISO(d.date), "dd/MM/yyyy")
            }
        },
        {
            Header: 'Cliente',
            id: 'customer-socialname',
            accessor: row => row.customer.social_name
        },
        {
            Header: 'Cidade',
            accessor: row => `${row.customer.adress_city}/${row.customer.adress_state}`
        },
        {
            accessor: 'value',
            Cell: (props: any) => <div style={{ textAlign: "right" }}>{props.value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</div>,
            Header: () => (
                <div style={{ textAlign: "right" }}>Valor</div>)

        },
        {
            accessor: 'falta_faturar',
            Cell: (props: any) => <div style={{ textAlign: "right" }}>{props.value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</div>,
            Header: () => (
                <div style={{ textAlign: "right" }}>A Faturar</div>)
        }
    ], []);

    const title = 'Lista de Notas Fiscais';

    const [selectedRows, setSelectedRows] = React.useState<any[]>([{}]);
    const [totalSaldoSelecionado, setTotalSaldoSelecionado] = React.useState(0);

    React.useEffect(() => {

        if (selectedRows.length > 0) {
            const valorCalculado = selectedRows.reduce((acc, cur) => {

                return { falta_faturar: acc.falta_faturar + cur.falta_faturar };
            });

            setTotalSaldoSelecionado(valorCalculado.falta_faturar);
        } else {
            setTotalSaldoSelecionado(0);
        }
    }, [selectedRows])

    return (
        <Layout title={title}>
            <div className="flex items-center justify-between mb-6">
                <h1 className="mb-1 text-2xl font-bold">{title}</h1>
                <button
                    type="button"
                    className="bg-green-500 hover:bg-green-700 text-white font-bold mx-4 py-2 px-4 rounded-full inline-flex items-center" >
                    <FiFilter size={18}></FiFilter>
                    <span className="ml-2">Filtro</span>
                </button>
            </div>

            <Form ref={formFiltroRef} onSubmit={handleSubmit} >
                <div className="bg-white rounded shadow p-2 mb-8">
                    <div className="mx-2 flex">
                        <div className="flex-1 mr-1">
                            <label htmlFor="customer" className="mb-2">Clientes (somente ativos)</label>
                            <Select
                                className="mt-1"
                                name="customer"
                                options={clientes}
                                isClearable
                            />
                        </div>

                        <div className="w-1/4 px-1">
                            <label htmlFor="agente" className="mb-2">Agente</label>
                            <Select
                                className="mt-1"
                                name="agente"
                                options={agents}
                                isClearable
                            />
                        </div>

                        <div className="w-1/4 pl-1">
                            <label htmlFor="agente2">Agente 2</label>
                            <Select
                                className="mt-1"
                                name="agente2"
                                options={agents2}
                                isClearable
                            />
                        </div>
                    </div>
                    <div className="mx-2 mt-1 flex">
                        <div className="w-1/4 mr-2">
                            <Input
                                name="nf_ini"
                                label="Nº Nota Inicial"
                            />
                        </div>

                        <div className="w-1/4 mr-2">
                            <Input
                                name="nf_fin"
                                label="Nº Nota Final"
                            />
                        </div>

                        <div className="w-1/4 mr-2">
                            <DatePicker
                                name="data_ini"
                                label="Data Inicial"
                            />
                        </div>
                        <div className="w-1/4">
                            <DatePicker
                                name="data_fin"
                                label="Data Final"
                            />
                        </div>
                    </div>
                    <div className="mx-2 mt-1 flex">
                        <div className="w-1/4 mr-2">
                            <Input
                                name="saldo_ini"
                                label="Saldo Inicial"
                            />
                        </div>

                        <div className="w-1/4 mr-2">
                            <Input
                                name="saldo_fin"
                                label="Saldo Final"
                            />
                        </div>

                        <div className="w-1/4 mr-2">
                            <NumberInput
                                prefix={'R$'}
                                name="saldo_cli_ini"
                                label="Saldo Cliente Inicial"
                            />
                        </div>

                        <div className="w-1/4">
                            <NumberInput
                                prefix={'R$'}
                                name="saldo_cli_fin"
                                label="Saldo Cliente Final"
                            />
                        </div>
                    </div>
                    <div className="my-4 justify-end flex">
                        <button
                            type="submit"
                            className="bg-green-500 hover:bg-green-700 text-white font-bold mx-4 py-2 px-4 rounded-full inline-flex items-center" >
                            <FiFilter size={18}></FiFilter>
                            <span className="ml-2">Filtro</span>
                        </button>
                    </div>

                </div>
            </Form>

            <div className="bg-white rounded shadow">
                <Table<Invoice>
                    //columns={columns}
                    name="teste"
                    columns={columns}
                    data={invoices}
                    setSelectedRows={setSelectedRows}
                />
            </div>

            <div className="my-4 bg-white rounded shadow">
                <h1 className="mb-1 text-xl font-bold p-4">Dados do Lote</h1>
                <Form ref={formLoteRef} onSubmit={handleSubmitLote} className="flex flex-col flex-1 w-full ">
                    <div className="mx-2 flex flex-auto space-x-2 justify-items-stretch items-end pb-4">
                        <Input
                            name="codigo_lote"
                            label="Código do Lote"
                            style={{ flex: "auto" }}
                        />
                        <DatePicker
                            name="data_vencimento"
                            label="Data de Vencimento"
                            className="flex-auto"
                        />
                        <NumberInput
                            prefix={'R$ '}
                            name="total_lote"
                            label="Valor Total do Lote"
                            style={{ flex: "auto" }}
                        />
                        <NumberInput
                            prefix={'R$ '}
                            name="valor_maximo_cobranca"
                            label="Valor Máximo Cobrança"
                            style={{ flex: "auto" }}
                        />
                        <NumberInput
                            prefix={'R$ '}
                            name="totalSelecionado"
                            label="Total Selecionado"
                            disabled
                            value={totalSaldoSelecionado}
                            className="flex-auto"
                        />
                    </div>
                    <div className="mx-2 flex space-x-2 justify-items-stretch items-end pb-4">

                        <Input
                            className="flex-1"
                            name="email"
                            label="E-mail"
                            style={{ flex: "auto" }}
                        />

                        <div className="flex-1">
                            <label htmlFor="agente" className="mb-2">Conta Corrente</label>
                            <Select
                                className="mt-1"
                                name="conta_corrente"
                                options={contasCorrentes}
                            />
                        </div>
                        <div className="">
                            <button
                                type="submit"
                                className="bg-green-500 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded inline-flex items-center" >
                                <FiFilter size={18}></FiFilter>
                                <span className="ml-2">Gerar Lote</span>
                            </button>
                        </div>
                    </div>
                </Form>
            </div>

        </Layout>
    );
};

export default Index;





