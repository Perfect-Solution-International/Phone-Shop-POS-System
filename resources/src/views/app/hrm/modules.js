const employee = { key: 'employee_id', label: 'Employee', type: 'employee', rules: 'required', default: null };
const status = (values) => ({ key: 'status', label: 'Status', type: 'select', options: values, rules: 'required', default: values[0] });
const money = (key, label) => ({ key, label, type: 'text', rules: { required: true, regex: /^\d{1,8}(\.\d{1,2})?$/ }, default: '0.00', inputmode: 'decimal' });
const column = (field, label, sortable = true) => ({ field, label, sortable });

export default {
    employees: {
        title: 'Employees', permission: 'hrm.manage',
        columns: [column('employee_code', 'Code'), column('name', 'Name'), column('email', 'Email'), column('department', 'Department'), column('joined_on', 'Joined')],
        fields: [
            { key: 'employee_code', label: 'Employee code', rules: 'required|max:50', default: '' },
            { key: 'name', label: 'Name', rules: 'required|max:255', default: '' },
            { key: 'email', label: 'Email address', type: 'email', rules: 'required|email|max:255', default: '' },
            { key: 'department', label: 'Department', rules: 'max:100', default: '' },
            { key: 'position', label: 'Position', rules: 'max:100', default: '' },
            { key: 'joined_on', label: 'Joined on', type: 'date', rules: 'required', default: '' },
            { key: 'is_active', label: 'Active employee', type: 'checkbox', default: true },
        ],
    },
    attendance: {
        title: 'Attendance', permission: 'hrm.manage',
        columns: [column('employee.name', 'Employee', false), column('work_date', 'Work date'), column('clocked_in_at', 'Clock in'), column('clocked_out_at', 'Clock out')],
        fields: [
            employee,
            { key: 'work_date', label: 'Work date', type: 'date', rules: 'required', default: '' },
            { key: 'clocked_in_at', label: 'Clock in (your device time)', type: 'datetime-local', rules: 'required', default: '' },
            { key: 'clocked_out_at', label: 'Clock out (your device time)', type: 'datetime-local', default: '' },
        ],
    },
    leave: {
        title: 'Leave', permission: 'hrm.manage',
        columns: [column('employee.name', 'Employee', false), column('type', 'Type'), column('starts_on', 'From'), column('ends_on', 'To'), column('status', 'Status')],
        fields: [
            employee,
            { key: 'starts_on', label: 'Start date', type: 'date', rules: 'required', default: '' },
            { key: 'ends_on', label: 'End date', type: 'date', rules: 'required', default: '' },
            { key: 'type', label: 'Leave type', type: 'select', options: ['annual', 'sick', 'unpaid'], rules: 'required', default: 'annual' },
            { key: 'reason', label: 'Reason', type: 'textarea', rules: 'max:2000', default: '' },
            status(['pending', 'approved', 'rejected']),
        ],
    },
    payroll: {
        title: 'Payroll', permission: 'payroll.manage',
        columns: [column('employee.name', 'Employee', false), column('period', 'Period'), column('basic_salary', 'Basic salary'), column('allowances', 'Allowances'), column('deductions', 'Deductions'), column('net_salary', 'Net salary'), column('status', 'Status')],
        fields: [
            employee,
            { key: 'period', label: 'Payroll month', type: 'month', rules: 'required', default: '' },
            money('basic_salary', 'Basic salary'),
            money('allowances', 'Allowances'),
            money('deductions', 'Deductions'),
            status(['draft', 'ready']),
        ],
    },
};
